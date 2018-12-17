<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Framework;

use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface OrderListRelationRepositoryInterface
{
    /**
     * @param int $listId
     * @param OwnershipContext $ownershipContext
     * @return string
     */
    public function fetchOrderListNameForListId(int $listId, OwnershipContext $ownershipContext): string;

    /**
     * @param int $listId
     * @param string $productNumber
     * @param OwnershipContext $ownershipContext
     * @return string
     */
    public function fetchOrderListNameForPositionNumber(int $listId, string $productNumber, OwnershipContext $ownershipContext): string;

    /**
     * @param LineItemList $list
     * @param string $orderListName
     */
    public function addOrderListToCartAttribute(LineItemList $list, string $orderListName);
}
