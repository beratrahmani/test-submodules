<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

use Shopware\B2B\Common\B2BException;

class IsNoAuditLogIndexEntityException extends \DomainException implements B2BException
{
}
