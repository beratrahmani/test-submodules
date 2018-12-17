<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Shopware\B2B\Common\B2BException;

class InsufficientBudgetException extends \DomainException implements B2BException
{
}
