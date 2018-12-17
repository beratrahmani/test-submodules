<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Framework;

use Shopware\B2B\Common\Entity;

abstract class OrderItemEntity implements Entity
{
    /**
     * @todo remove since it is not used across all implementations
     * @var mixed
     */
    public $identifier;
}
