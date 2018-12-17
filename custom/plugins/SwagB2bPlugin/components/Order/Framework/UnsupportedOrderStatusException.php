<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Shopware\B2B\Common\B2BException;

class UnsupportedOrderStatusException extends \InvalidArgumentException implements B2BException
{
}
