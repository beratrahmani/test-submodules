<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogValueDiffEntity;

class AuditLogValueOfferDiffEntity extends AuditLogValueDiffEntity
{
    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'OfferStatusChange';
    }
}
