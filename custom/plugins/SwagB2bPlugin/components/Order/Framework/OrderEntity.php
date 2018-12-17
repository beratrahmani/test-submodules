<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Shopware\B2B\LineItemList\Framework\LineItemList;

class OrderEntity extends OrderContext
{
    /**
     * @var LineItemList
     */
    public $list;
}
