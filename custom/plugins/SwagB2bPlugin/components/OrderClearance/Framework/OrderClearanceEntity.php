<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Framework;

use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserPostalSettings;

class OrderClearanceEntity extends OrderContext
{
    /**
     * @var LineItemList
     */
    public $list;

    /**
     * @var UserPostalSettings|null
     */
    public $userPostalSettings = null;

    /**
     * @var bool
     */
    public $isClearable = false;

    /**
     * @var OrderItemEntity[]
     */
    public $items = [];

    /**
     * @param string $className
     * @return OrderItemEntity[]
     */
    public function getItemsOfType(string $className): array
    {
        return array_filter($this->items, function (OrderItemEntity $itemEntity) use ($className) {
            return $itemEntity instanceof $className;
        });
    }
}
