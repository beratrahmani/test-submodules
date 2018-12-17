<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

use Shopware\B2B\Common\B2BException;

class UnsupportedQuantityException extends \DomainException implements B2BException
{
}
