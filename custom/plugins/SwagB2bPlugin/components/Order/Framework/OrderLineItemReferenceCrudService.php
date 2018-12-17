<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceRepository;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceValidationService;
use Shopware\B2B\OrderNumber\Framework\OrderNumberRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderLineItemReferenceCrudService extends AbstractCrudService
{
    /**
     * @var LineItemReferenceRepository
     */
    private $lineItemReferenceRepository;

    /**
     * @var OrderLineItemAuditLogService
     */
    private $lineItemAuditLogService;

    /**
     * @var LineItemReferenceValidationService
     */
    private $validationService;

    /**
     * @var LineItemListService
     */
    private $lineItemListService;

    /**
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @param LineItemListService $lineItemListService
     * @param LineItemReferenceValidationService $validationService
     * @param LineItemReferenceRepository $lineItemReferenceRepository
     * @param OrderLineItemAuditLogService $lineItemAuditLogService
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     */
    public function __construct(
        LineItemListService $lineItemListService,
        LineItemReferenceValidationService $validationService,
        LineItemReferenceRepository $lineItemReferenceRepository,
        OrderLineItemAuditLogService $lineItemAuditLogService,
        OrderNumberRepositoryInterface $orderNumberRepository
    ) {
        $this->lineItemListService = $lineItemListService;
        $this->lineItemReferenceRepository = $lineItemReferenceRepository;
        $this->lineItemAuditLogService = $lineItemAuditLogService;
        $this->validationService = $validationService;
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
        ]);
    }

    /**
     * @param int $listId
     * @param int $lineItemId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     */
    public function deleteLineItem(
        int $listId,
        int $lineItemId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ) {
        $item = $this->lineItemReferenceRepository
            ->fetchReferenceById($lineItemId);

        $this->lineItemAuditLogService
            ->createDeleteLineItem($listId, $item);

        $this->lineItemReferenceRepository
            ->removeReference($lineItemId);

        $this->lineItemListService
            ->updateListPricesById($listId, $currencyContext, $ownershipContext);
    }

    /**
     * @param int $lineItemIdOne
     * @param int $lineItemIdTwo
     */
    public function flipLineItemSorting(int $lineItemIdOne, int $lineItemIdTwo)
    {
        $lineItemOne = $this->lineItemReferenceRepository
            ->fetchReferenceById($lineItemIdOne);

        $lineItemTwo = $this->lineItemReferenceRepository
            ->fetchReferenceById($lineItemIdTwo);

        $this->lineItemReferenceRepository
            ->flipSorting($lineItemOne, $lineItemTwo);
    }

    /**
     * @param int $listId
     * @param int $lineItemId
     * @param OrderContext $orderContext
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     */
    public function deleteLineItemFromOrder(
        int $listId,
        int $lineItemId,
        OrderContext $orderContext,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ) {
        if ($orderContext->statusId !== -2) {
            throw new UnsupportedOrderStatusException('You should not be able to delete and item of an open Order');
        }

        $this->deleteLineItem($listId, $lineItemId, $currencyContext, $ownershipContext);
    }

    /**
     * @param int $listId
     * @param CrudServiceRequest $request
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return LineItemReference
     */
    public function updateLineItem(
        int $listId,
        CrudServiceRequest $request,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): LineItemReference {
        $currentItem = $this->lineItemReferenceRepository
            ->fetchReferenceById((int) $request->requireParam('id'));

        $updatedItem = clone $currentItem;
        $updatedItem->setData($request->getFilteredData());

        $validations = $this->validationService
            ->createUpdateValidation($updatedItem);

        $this->testValidation($updatedItem, $validations);

        $this->lineItemReferenceRepository
            ->updateReference($listId, $updatedItem);

        $list = $this->lineItemListService
            ->updateListPricesById($listId, $currencyContext, $ownershipContext);

        $this->lineItemAuditLogService
            ->createLineItemChange($listId, $currentItem, $updatedItem->quantity);

        $this->lineItemAuditLogService
            ->createLineItemComment($listId, $currentItem, $updatedItem->comment);

        return $list->getReferenceById($updatedItem->id);
    }

    /**
     * @param int $listId
     * @param CrudServiceRequest $request
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return LineItemReference
     */
    public function addLineItem(
        int $listId,
        CrudServiceRequest $request,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): LineItemReference {
        $lineItemReference = new LineItemReference();
        $lineItemReference->setData($request->getFilteredData());
        $lineItemReference->mode = 0;

        if ($lineItemReference->referenceNumber) {
            $lineItemReference->referenceNumber = $this->orderNumberRepository->fetchOriginalOrderNumber($lineItemReference->referenceNumber, $ownershipContext);
        }

        try {
            $existingReference  = $this->lineItemReferenceRepository->getReferenceByReferenceNumberAndListId($lineItemReference->referenceNumber, $listId);

            $lineItemReference->quantity += $existingReference->quantity;

            $validations = $this->validationService
                ->createInsertValidation($lineItemReference, $listId);

            $lineItemReference->quantity -= $existingReference->quantity;
        } catch (NotFoundException $exception) {
            $validations = $this->validationService
                ->createInsertValidation($lineItemReference, $listId);
        }

        $this->testValidation($lineItemReference, $validations);

        $list = new LineItemList();
        $list->id = $listId;
        $list->references = [$lineItemReference];

        $this->lineItemListService->updateListReferences($list, $currencyContext, $ownershipContext);

        $this->lineItemAuditLogService
            ->createAddLineItem($listId, $lineItemReference);

        return $list->getReferenceById($lineItemReference->id);
    }
}
