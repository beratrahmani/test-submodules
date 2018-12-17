<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

class AuditLogValueOrderReferenceEntity extends AuditLogValueDiffEntity
{
    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'OrderClearanceOrderReference';
    }
}
