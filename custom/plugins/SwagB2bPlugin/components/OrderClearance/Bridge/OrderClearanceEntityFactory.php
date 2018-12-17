<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Bridge;

use Shopware\B2B\LineItemList\Bridge\LineItemCheckoutProvider;
use Shopware\B2B\LineItemList\Bridge\LineItemCheckoutSource;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceEntity;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceEntityFactoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderClearanceEntityFactory implements OrderClearanceEntityFactoryInterface
{
    /**
     * @var OrderItemLoaderInterface[]
     */
    private $itemLoaders;

    /**
     * @var LineItemCheckoutProvider
     */
    private $listProvider;

    /**
     * @param LineItemCheckoutProvider $listProvider
     * @param OrderItemLoaderInterface[] ...$itemLoaders
     */
    public function __construct(LineItemCheckoutProvider $listProvider, OrderItemLoaderInterface ...$itemLoaders)
    {
        $this->listProvider = $listProvider;
        $this->itemLoaders = $itemLoaders;
    }

    /**
     * @param array $databaseArray
     * @param OwnershipContext $ownershipContext
     * @return OrderClearanceEntity
     */
    public function createOrderEntityFromDatabase(array $databaseArray, OwnershipContext $ownershipContext): OrderClearanceEntity
    {
        $order = $this->createOrder();
        $this->fromDatabaseArray($order, $databaseArray);
        $this->extendStoredOrderWithItems($order, $ownershipContext);

        return $order;
    }

    /**
     * @param array $basketArray
     * @param OwnershipContext $ownershipContext
     * @return OrderClearanceEntity
     */
    public function createOrderEntityFromBasketArray(array $basketArray, OwnershipContext $ownershipContext): OrderClearanceEntity
    {
        $order = $this->createOrder();
        $this->fromSessionArray($order, $basketArray);
        $this->extendSessionOrderWithItems($order, $basketArray, $ownershipContext);

        return $order;
    }

    /**
     * @param OrderClearanceEntity $entity
     * @param array $data
     */
    private function fromDatabaseArray(OrderClearanceEntity $entity, array $data)
    {
        $entity->fromDatabaseArray($data);
    }

    /**
     * @param OrderClearanceEntity $entity
     * @param array $sGetBasket
     */
    private function fromSessionArray(OrderClearanceEntity $entity, array $sGetBasket)
    {
        $entity->id = null;
        $entity->orderNumber = null;
        $entity->comment = null;
        $entity->status = null;

        $checkoutSource = new LineItemCheckoutSource($sGetBasket);

        $entity->list = $this->listProvider->createList($checkoutSource);
    }

    /**
     * @return OrderClearanceEntity
     */
    private function createOrder(): OrderClearanceEntity
    {
        return new OrderClearanceEntity();
    }

    /**
     * @param OrderClearanceEntity $order
     * @param OwnershipContext $ownershipContext
     */
    private function extendStoredOrderWithItems(OrderClearanceEntity $order, OwnershipContext $ownershipContext)
    {
        $items = [];
        foreach ($this->itemLoaders as $itemLoader) {
            $items = array_merge($items, $itemLoader->fetchItemsFromStorage($order, $ownershipContext));
        }

        $order->items = $items;
    }

    /**
     * @param OrderClearanceEntity $order
     * @param array $basket
     * @param OwnershipContext $ownershipContext
     */
    private function extendSessionOrderWithItems(OrderClearanceEntity $order, array $basket, OwnershipContext $ownershipContext)
    {
        $items = [];

        foreach ($this->itemLoaders as $itemLoader) {
            $items = array_merge($items, $itemLoader->fetchItemsFromBasketArray($order, $basket, $ownershipContext));
        }

        $order->items = $items;
    }
}
