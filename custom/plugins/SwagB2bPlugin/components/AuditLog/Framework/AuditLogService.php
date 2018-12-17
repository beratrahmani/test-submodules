<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class AuditLogService
{
    /**
     * @var AuditLogAuthorService
     */
    private $auditLogAuthorService;

    /**
     * @var AuditLogRepository
     */
    private $auditLogRepository;

    /**
     * @param AuditLogAuthorService $auditLogAuthorService
     * @param AuditLogRepository $auditLogRepository
     */
    public function __construct(
        AuditLogAuthorService $auditLogAuthorService,
        AuditLogRepository $auditLogRepository
    ) {
        $this->auditLogAuthorService = $auditLogAuthorService;
        $this->auditLogRepository = $auditLogRepository;
    }

    /**
     * @param AuditLogEntity $auditLogEntity
     * @param Identity $identity
     * @param AuditLogIndexEntity[] $auditLogIndex
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     * @return AuditLogEntity
     */
    public function createAuditLog(
        AuditLogEntity $auditLogEntity,
        Identity $identity,
        array $auditLogIndex
    ): AuditLogEntity {
        $authorEntity = $this->auditLogAuthorService
            ->createAuthorEntityFromIdentity($identity);

        $auditLogEntity->authorHash = $authorEntity->hash;

        $this->auditLogRepository
            ->createAuditLog($auditLogEntity, $auditLogIndex);

        return $auditLogEntity;
    }

    /**
     * @param AuditLogEntity $auditLogEntity
     * @param array $auditLogIndex
     * @return AuditLogEntity
     */
    public function createBackendAuditLog(
        AuditLogEntity $auditLogEntity,
        array $auditLogIndex
    ): AuditLogEntity {
        $authorEntity = $this->auditLogAuthorService->createBackendAuthorEntity();

        $auditLogEntity->authorHash = $authorEntity->hash;

        $this->auditLogRepository
            ->createAuditLog($auditLogEntity, $auditLogIndex);

        return $auditLogEntity;
    }
}
