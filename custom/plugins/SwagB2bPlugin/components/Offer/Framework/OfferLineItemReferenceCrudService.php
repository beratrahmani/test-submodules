<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\OrderNumber\Framework\OrderNumberRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class OfferLineItemReferenceCrudService extends AbstractCrudService
{
    /**
     * @var OfferLineItemReferenceRepository
     */
    private $offerLineItemReferenceRepository;

    /**
     * @var LineItemListService
     */
    private $lineItemListService;

    /**
     * @var OfferLineItemReferenceValidationService
     */
    private $validationService;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferLineItemListRepository
     */
    private $offerLineItemListRepository;

    /**
     * @var TaxProviderInterface
     */
    private $taxProvider;

    /**
     * @var OfferLineItemAuditLogService
     */
    private $offerLineItemAuditLogService;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @param OfferLineItemReferenceRepository $offerLineItemReferenceRepository
     * @param LineItemListService $lineItemListService
     * @param OfferService $offerService
     * @param OfferRepository $offerRepository
     * @param OfferLineItemReferenceValidationService $offerLineItemReferenceValidationService
     * @param OfferLineItemListRepository $offerLineItemListRepository
     * @param TaxProviderInterface $taxProvider
     * @param OfferLineItemAuditLogService $offerLineItemAuditLogService
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     */
    public function __construct(
        OfferLineItemReferenceRepository $offerLineItemReferenceRepository,
        LineItemListService $lineItemListService,
        OfferService $offerService,
        OfferRepository $offerRepository,
        OfferLineItemReferenceValidationService $offerLineItemReferenceValidationService,
        OfferLineItemListRepository $offerLineItemListRepository,
        TaxProviderInterface $taxProvider,
        OfferLineItemAuditLogService $offerLineItemAuditLogService,
        OrderNumberRepositoryInterface $orderNumberRepository
    ) {
        $this->offerLineItemReferenceRepository = $offerLineItemReferenceRepository;
        $this->lineItemListService = $lineItemListService;
        $this->offerService = $offerService;
        $this->offerRepository = $offerRepository;
        $this->validationService = $offerLineItemReferenceValidationService;
        $this->offerLineItemListRepository = $offerLineItemListRepository;
        $this->taxProvider = $taxProvider;
        $this->offerLineItemAuditLogService = $offerLineItemAuditLogService;
        $this->orderNumberRepository = $orderNumberRepository;
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createCreateCrudRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest($data, [
            'referenceNumber',
            'quantity',
            'comment',
            'discountAmountNet',
        ]);
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createUpdateCrudRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest($data, [
            'id',
            'referenceNumber',
            'quantity',
            'comment',
            'discountAmountNet',
        ]);
    }

    /**
     * @param int $listId
     * @param int $offerId
     * @param CrudServiceRequest $request
     * @param CurrencyContext $currencyContext
     * @param Identity $identity
     * @param bool $isBackend
     * @return LineItemReference
     */
    public function updateLineItem(
        int $listId,
        int $offerId,
        CrudServiceRequest $request,
        CurrencyContext $currencyContext,
        Identity $identity,
        bool $isBackend = false
    ): LineItemReference {
        $data = $request->getFilteredData();

        $currentItem = $this->offerLineItemReferenceRepository
            ->fetchReferenceById((int) $data['id'], $identity->getOwnershipContext());

        /** @var OfferLineItemReferenceEntity $updatedItem */
        $updatedItem = clone $currentItem;
        $updatedItem->setData($data);

        $validations = $this->validationService
            ->createUpdateValidation($updatedItem, $offerId);

        $this->testValidation($updatedItem, $validations);

        $updatedItem->discountAmount = $updatedItem->discountAmountNet * $this->taxProvider->getProductTax($updatedItem);
        $updatedItem->discountCurrencyFactor = $currencyContext->currentCurrencyFactor;

        $this->offerLineItemReferenceRepository
            ->updateReference($listId, $updatedItem);

        $list = $this->offerLineItemListRepository->fetchOneListById($listId, $currencyContext, $identity->getOwnershipContext());

        $this->lineItemListService
            ->updateListPricesById($listId, $currencyContext, $identity->getOwnershipContext());

        $this->offerService->updateOfferPrices($offerId, $list, $currencyContext, $identity->getOwnershipContext());

        if ($currentItem->quantity !== $updatedItem->quantity) {
            $this->offerLineItemAuditLogService->createLineItemChange($listId, $currentItem, $updatedItem->quantity);
        }

        if (round($currentItem->discountAmountNet, 2) !== round($updatedItem->discountAmountNet, 2)) {
            $this->offerLineItemAuditLogService->createLineItemPriceChange($listId, $currentItem, (float) $updatedItem->discountAmountNet, $currencyContext);
        }

        if ($isBackend) {
            $this->setAdminChange($offerId);
        } else {
            $this->setUserChange($offerId);
        }

        return $list->getReferenceById($updatedItem->id);
    }

    /**
     * @param int $listId
     * @param int $offerId
     * @param CrudServiceRequest $request
     * @param CurrencyContext $currencyContext
     * @param Identity $identity
     * @param bool $isBackend
     * @return LineItemReference
     */
    public function addLineItem(
        int $listId,
        int $offerId,
        CrudServiceRequest $request,
        CurrencyContext $currencyContext,
        Identity $identity,
        bool $isBackend = false
    ): LineItemReference {
        $lineItemReference = new OfferLineItemReferenceEntity();
        $lineItemReference->setData($request->getFilteredData());

        if ($lineItemReference->referenceNumber && !$isBackend) {
            $lineItemReference->referenceNumber = $this->orderNumberRepository->fetchOriginalOrderNumber($lineItemReference->referenceNumber, $identity->getOwnershipContext());
        }

        $lineItemReference->mode = 0;

        $validations = $this->validationService
            ->createInsertValidation($lineItemReference, $listId);

        $this->testValidation($lineItemReference, $validations);

        $tax = $this->taxProvider->getProductTax($lineItemReference);

        $lineItemReference->discountAmount = $lineItemReference->discountAmountNet * $tax;

        $lineItemReference = $this->offerLineItemReferenceRepository
            ->addReference($listId, $lineItemReference);

        $this->lineItemListService->updateListPricesById($listId, $currencyContext, $identity->getOwnershipContext());

        if (!$lineItemReference->discountAmount) {
            $lineItemReference = $this->offerLineItemReferenceRepository
                ->setDefaultPricesForDiscountForLineItemReferenceId($lineItemReference->id, $identity->getOwnershipContext());
        }

        $list = $this->offerLineItemListRepository->fetchOneListById($listId, $currencyContext, $identity->getOwnershipContext());

        $this->offerService->updateOfferPrices($offerId, $list, $currencyContext, $identity->getOwnershipContext());

        $this->offerLineItemAuditLogService->createAddLineItem($listId, $lineItemReference);

        if ($isBackend) {
            $this->setAdminChange($offerId);
        } else {
            $this->setUserChange($offerId);
        }

        return $list->getReferenceById($lineItemReference->id);
    }

    /**
     * @param int $offerId
     * @param int $listId
     * @param int $lineItemId
     * @param CurrencyContext $currencyContext
     * @param Identity $identity
     * @param bool $isBackend
     * @return LineItemReference
     */
    public function deleteLineItem(
        int $offerId,
        int $listId,
        int $lineItemId,
        CurrencyContext $currencyContext,
        Identity $identity,
        bool $isBackend = false
    ): LineItemReference {
        $context = null;
        if ($identity) {
            $context = $identity->getOwnershipContext();
        }

        $currentItem = $this->offerLineItemReferenceRepository
            ->fetchReferenceById($lineItemId, $context);

        $this->offerLineItemReferenceRepository
            ->removeReference($lineItemId);

        $this->lineItemListService
            ->updateListPricesById($listId, $currencyContext, $identity->getOwnershipContext());

        $list = $this->offerLineItemListRepository->fetchOneListById($listId, $currencyContext, $context);

        $this->offerService->updateOfferPrices($offerId, $list, $currencyContext, $identity->getOwnershipContext());

        $this->offerLineItemAuditLogService->createDeleteLineItem($listId, $currentItem);

        $currentItem->id = null;
        $currentItem->quantity = 0;

        if ($isBackend) {
            $this->setAdminChange($offerId);
        } else {
            $this->setUserChange($offerId);
        }

        return $currentItem;
    }

    /**
     * @internal
     * @param int $offerId
     */
    protected function setAdminChange(int $offerId)
    {
        $offer = new OfferEntity();
        $offer->id = $offerId;
        $offer->updateDates(['changedByAdminAt']);

        $this->offerRepository->updateOfferDates($offer);
    }

    /**
     * @internal
     * @param int $offerId
     */
    protected function setUserChange(int $offerId)
    {
        $offer = new OfferEntity();
        $offer->id = $offerId;
        $offer->updateDates(['changedByUserAt']);

        $this->offerRepository->updateOfferDates($offer);
    }
}
