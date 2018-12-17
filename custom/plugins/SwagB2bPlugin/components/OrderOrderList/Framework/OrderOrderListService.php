<?php declare(strict_types=1);

namespace Shopware\B2B\OrderOrderList\Framework;

use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\OrderList\Framework\OrderListCrudService;
use Shopware\B2B\OrderList\Framework\OrderListEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderOrderListService
{
    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var OrderListCrudService
     */
    private $orderListCrudService;

    /**
     * @var LineItemListService
     */
    private $lineItemListService;

    /**
     * @param LineItemListRepository $lineItemListRepository
     * @param LineItemListService $lineItemListService
     * @param OrderListCrudService $orderListCrudService
     */
    public function __construct(
        LineItemListRepository $lineItemListRepository,
        LineItemListService $lineItemListService,
        OrderListCrudService $orderListCrudService
    ) {
        $this->lineItemListRepository = $lineItemListRepository;
        $this->orderListCrudService = $orderListCrudService;
        $this->lineItemListService = $lineItemListService;
    }

    /**
     * @param OrderContext $orderContext
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return OrderListEntity
     */
    public function createOrderListFromOrderContext(
        OrderContext $orderContext,
        OwnershipContext $ownershipContext,
        CurrencyContext $currencyContext
    ): OrderListEntity {
        $newRecordRequest = $this->orderListCrudService
            ->createNewRecordRequest(['name' => $orderContext->orderNumber ?? '-']);

        $orderedList = $this->lineItemListRepository
            ->fetchOneListById($orderContext->listId, $currencyContext, $ownershipContext);

        $this->resetListStorageIds($orderedList);
        $this->filterProducts($orderedList);

        $orderListEntity = $this->orderListCrudService
            ->create($newRecordRequest, $ownershipContext);

        $orderListEntity->lineItemList->references = $orderedList->references;

        $this->lineItemListService
            ->updateListReferences($orderListEntity->lineItemList, $currencyContext, $ownershipContext);

        return $orderListEntity;
    }

    /**
     * @internal
     * @param $lineItemList
     */
    protected function resetListStorageIds(LineItemList $lineItemList)
    {
        $lineItemList->id = null;
        foreach ($lineItemList->references as $reference) {
            $reference->id = null;
        }
    }

    /**
     * @internal
     * @param $lineItemList
     */
    protected function filterProducts(LineItemList $lineItemList)
    {
        $productReferences = [];

        foreach ($lineItemList->references as $reference) {
            if ($reference->mode !== 0) {
                continue;
            }
            $productReferences[] = $reference;
        }

        $lineItemList->references = $productReferences;
    }
}
