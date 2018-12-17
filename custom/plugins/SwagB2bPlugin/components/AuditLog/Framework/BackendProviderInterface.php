<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

interface BackendProviderInterface
{
    /**
     * @return AuditLogAuthorEntity
     */
    public function getBackendUser(): AuditLogAuthorEntity;
}
