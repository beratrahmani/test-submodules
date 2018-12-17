<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OfferCrudService extends AbstractCrudService
{
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferValidationService
     */
    private $validationService;

    /**
     * @var OfferLineItemListRepository
     */
    private $offerLineItemListRepository;

    /**
     * @var LineItemListService
     */
    private $lineItemListService;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @param OfferRepository $offerRepository
     * @param OfferValidationService $validationService
     * @param OfferLineItemListRepository $offerLineItemListRepository
     * @param LineItemListService $lineItemListService
     * @param OfferService $offerService
     */
    public function __construct(
        OfferRepository $offerRepository,
        OfferValidationService $validationService,
        OfferLineItemListRepository $offerLineItemListRepository,
        LineItemListService $lineItemListService,
        OfferService $offerService
    ) {
        $this->offerRepository = $offerRepository;
        $this->validationService = $validationService;
        $this->offerLineItemListRepository = $offerLineItemListRepository;
        $this->lineItemListService = $lineItemListService;
        $this->offerService = $offerService;
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createNewRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'orderContextId',
                'listId',
            ]
        );
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createExistingRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'id',
                'discountValueNet',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return OfferEntity
     */
    public function create(
        CrudServiceRequest $request,
        OwnershipContext $ownershipContext,
        CurrencyContext $currencyContext
    ): OfferEntity {
        $data = $request->getFilteredData();
        $data['authId'] = $ownershipContext->authId;

        $offer = new OfferEntity();

        $offer->setData($data);
        $offer->updateDates(['createdAt']);
        $offer->status = OfferEntity::STATE_OPEN;
        $offer->currencyFactor = $currencyContext->currentCurrencyFactor;

        $validation = $this->validationService
            ->createInsertValidation($offer, $ownershipContext);

        $this->testValidation($offer, $validation);

        $this->offerRepository->addOffer($offer);

        $this->offerRepository->updateOfferDates($offer);

        $this->lineItemListService
            ->updateListPricesById($offer->listId, $currencyContext, $ownershipContext);

        $list = $this->offerLineItemListRepository
            ->fetchOneListById($offer->listId, $currencyContext, $ownershipContext);

        $this->offerService->updateOfferPrices($offer->id, $list, $currencyContext, $ownershipContext);

        return $offer;
    }

    /**
     * @param CrudServiceRequest $request
     * @param CurrencyContext $currencyContext
     * @param Identity $identity
     * @param bool $isBackend
     * @return OfferEntity
     */
    public function update(
        CrudServiceRequest $request,
        CurrencyContext $currencyContext,
        Identity $identity,
        bool $isBackend
    ): OfferEntity {
        $data = $request->getFilteredData();

        $offer = $this->offerRepository->fetchOfferById($data['id'], $currencyContext, $identity->getOwnershipContext());

        $oldDiscount = $offer->discountValueNet;
        $offer->setData($data);

        $offer->currencyFactor = $currencyContext->currentCurrencyFactor;

        $validation = $this->validationService
            ->createUpdateValidation($offer, $identity->getOwnershipContext());

        $this->testValidation($offer, $validation);

        $ownershipContext = $identity->getOwnershipContext();

        $offer = $this->offerRepository
            ->updateOffer($offer);

        $this->lineItemListService
            ->updateListPricesById($offer->listId, $currencyContext, $ownershipContext);

        $list = $this->offerLineItemListRepository
            ->fetchOneListById($offer->listId, $currencyContext, $ownershipContext);

        if ($isBackend) {
            $this->setAdminChange($offer->id);
        } else {
            $this->setUserChange($offer->id);
        }

        if ($oldDiscount !== $offer->discountValueNet && isset($offer->discountValueNet)) {
            $this->offerService->createOfferDiscountLogEntry(
                $offer->orderContextId,
                $identity,
                $offer->discountValueNet,
                $oldDiscount,
                $currencyContext,
                $isBackend
            );
        }

        return $this->offerService
            ->updateOfferPrices($offer->id, $list, $currencyContext, $identity->getOwnershipContext());
    }

    /**
     * @param CrudServiceRequest $request
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return OfferEntity
     */
    public function remove(CrudServiceRequest $request, CurrencyContext $currencyContext, OwnershipContext $ownershipContext): OfferEntity
    {
        $data = $request->getFilteredData();

        $offer = $this->offerRepository->fetchOfferById((int) $data['id'], $currencyContext, $ownershipContext);

        $this->offerRepository
            ->removeOffer($offer, $ownershipContext);

        return $offer;
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
