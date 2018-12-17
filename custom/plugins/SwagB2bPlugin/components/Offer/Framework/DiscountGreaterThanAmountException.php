<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Common\B2BException;

class DiscountGreaterThanAmountException extends \DomainException implements B2BException
{
}
