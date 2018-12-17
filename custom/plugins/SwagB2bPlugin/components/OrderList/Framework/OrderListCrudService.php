<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Framework;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Acl\Framework\AclUnsupportedContextException;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderListCrudService extends AbstractCrudService
{
    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @var OrderListValidationService
     */
    private $validationService;

    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var LineItemListService
     */
    private $lineItemListService;

    /**
     * @var OrderListRelationRepositoryInterface
     */
    private $orderListRelationRepository;

    /**
     * @param OrderListRepository $orderListRepository
     * @param OrderListValidationService $validationService
     * @param AclRepository $aclRepository
     * @param LineItemListRepository $lineItemListRepository
     * @param LineItemListService $lineItemListService
     * @param OrderListRelationRepositoryInterface $orderListRelationRepository
     */
    public function __construct(
        OrderListRepository $orderListRepository,
        OrderListValidationService $validationService,
        AclRepository $aclRepository,
        LineItemListRepository $lineItemListRepository,
        LineItemListService $lineItemListService,
        OrderListRelationRepositoryInterface $orderListRelationRepository
    ) {
        $this->orderListRepository = $orderListRepository;
        $this->validationService = $validationService;
        $this->aclRepository = $aclRepository;
        $this->lineItemListRepository = $lineItemListRepository;
        $this->lineItemListService = $lineItemListService;
        $this->orderListRelationRepository = $orderListRelationRepository;
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
                'name',
                'budgetId',
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
                'name',
                'budgetId',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @return OrderListEntity
     */
    public function create(
        CrudServiceRequest $request,
        OwnershipContext $ownershipContext
    ): OrderListEntity {
        $data = $request->getFilteredData();
        $data['contextOwnerId'] = $ownershipContext->contextOwnerId;

        $lineItemList = new LineItemList();
        $lineItemList->contextOwnerId = $ownershipContext->contextOwnerId;

        $lineItemList = $this->lineItemListRepository
            ->addList($lineItemList, $ownershipContext);

        $data['listId'] = $lineItemList->id;

        $orderList = new OrderListEntity();
        $orderList->setData($data);

        $validation = $this->validationService
            ->createInsertValidation($orderList);

        $this->testValidation($orderList, $validation);

        $this->lineItemListService
            ->updateListPrices($lineItemList, $ownershipContext);

        $orderList = $this->orderListRepository
            ->addOrderList($orderList, $ownershipContext);

        $orderList->lineItemList = $lineItemList;

        try {
            $this->aclRepository->allow(
                $ownershipContext,
                (int) $orderList->id
            );
        } catch (AclUnsupportedContextException $e) {
            return $orderList;
        }

        return $orderList;
    }

    /**
     * @param int $orderListId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     */
    public function produceCart(int $orderListId, CurrencyContext $currencyContext, OwnershipContext $ownershipContext)
    {
        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $this->lineItemListService
            ->produceCart($orderList->listId, $currencyContext, $ownershipContext);

        $list = $this->lineItemListRepository
            ->fetchOneListById($orderList->listId, $currencyContext, $ownershipContext);

        $this->orderListRelationRepository->addOrderListToCartAttribute($list, $orderList->name);
    }

    /**
     * @param int $orderListId
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return OrderListEntity
     */
    public function duplicate(
        int $orderListId,
        OwnershipContext $ownershipContext,
        CurrencyContext $currencyContext
    ): OrderListEntity {
        $orderList = $this->orderListRepository->fetchOneById($orderListId, $currencyContext, $ownershipContext);
        $orderList->contextOwnerId = $ownershipContext->contextOwnerId;
        $orderList->id = null;

        $validation = $this->validationService
            ->createInsertValidation($orderList);

        $this->testValidation($orderList, $validation);

        $list = $this->createNewLineItemCopy($orderList->listId, $ownershipContext, $currencyContext);

        $this->addDuplicatedOrderList($orderList, $list, $ownershipContext);

        try {
            $this->aclRepository->allow(
                $ownershipContext,
                (int) $orderList->id
            );
        } catch (AclUnsupportedContextException $e) {
            return $orderList;
        }

        return $orderList;
    }

    /**
     * @param LineItemReference[] $references
     * @return array
     */
    protected function resetReferenceItemsIds(array $references): array
    {
        foreach ($references as $reference) {
            $reference->id = null;
        }

        return $references;
    }

    /**
     * @internal
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return LineItemList
     */
    protected function createNewLineItemCopy(
        int $id,
        OwnershipContext $ownershipContext,
        CurrencyContext $currencyContext
    ): LineItemList {
        $list = $this->lineItemListRepository->fetchOneListById($id, $currencyContext, $ownershipContext);
        $list->contextOwnerId = $ownershipContext->contextOwnerId;
        $list->id = null;

        $this->resetReferenceItemsIds($list->references);

        return $this->lineItemListService->createListThroughListObject($list, $ownershipContext);
    }

    /**
     * @internal
     * @param OrderListEntity $orderList
     * @param LineItemList $list
     * @param OwnershipContext $ownershipContext
     * @return OrderListEntity
     */
    protected function addDuplicatedOrderList(
        OrderListEntity $orderList,
        LineItemList $list,
        OwnershipContext $ownershipContext
    ): OrderListEntity {
        $orderList->listId = $list->id;

        return $this->orderListRepository->addOrderList($orderList, $ownershipContext);
    }

    /**
     * @param CrudServiceRequest $request
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return OrderListEntity
     */
    public function update(
        CrudServiceRequest $request,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): OrderListEntity {
        $data = $request->getFilteredData();

        $orderList = $this->orderListRepository
            ->fetchOneById((int) $request->requireParam('id'), $currencyContext, $ownershipContext);

        $orderList->setData($data);

        $validation = $this->validationService
            ->createUpdateValidation($orderList);

        $this->testValidation($orderList, $validation);

        return $this->orderListRepository
            ->updateOrderList($orderList, $ownershipContext);
    }

    /**
     * @param CrudServiceRequest $request
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return OrderListEntity
     */
    public function remove(CrudServiceRequest $request, CurrencyContext $currencyContext, OwnershipContext $ownershipContext): OrderListEntity
    {
        $orderListId = (int) $request->requireParam('id');
        $orderList = $this->orderListRepository->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $this->orderListRepository
            ->removeOrderList($orderList, $ownershipContext);

        return $orderList;
    }
}
