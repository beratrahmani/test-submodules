<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Bridge;

use Shopware\B2B\OrderClearance\Framework\OrderClearanceEntity;
use Shopware\B2B\OrderClearance\Framework\OrderItemEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface OrderItemLoaderInterface
{
    /**
     * @param OrderClearanceEntity $itemEntity
     * @param OwnershipContext $ownershipContext
     * @return OrderItemEntity[]
     */
    public function fetchItemsFromStorage(OrderClearanceEntity $itemEntity, OwnershipContext $ownershipContext): array;

    /**
     * @param OrderClearanceEntity $itemEntity
     * @param array $basketArray
     * @param OwnershipContext $ownershipContext
     * @return OrderItemEntity[]
     */
    public function fetchItemsFromBasketArray(OrderClearanceEntity $itemEntity, array $basketArray, OwnershipContext $ownershipContext): array;
}
