<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface OrderClearanceEntityFactoryInterface
{
    /**
     * @param array $databaseArray
     * @param OwnershipContext $ownershipContext
     * @return OrderClearanceEntity
     */
    public function createOrderEntityFromDatabase(array $databaseArray, OwnershipContext $ownershipContext): OrderClearanceEntity;

    /**
     * @param array $basketArray
     * @param OwnershipContext $ownershipContext
     * @return OrderClearanceEntity
     */
    public function createOrderEntityFromBasketArray(array $basketArray, OwnershipContext $ownershipContext): OrderClearanceEntity;
}
