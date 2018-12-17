<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogIndexEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogService;
use Shopware\B2B\AuditLog\Framework\AuditLogValueBasicEntity;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class OfferAuditLogService
{
    /**
     * @var AuditLogService
     */
    private $auditLogService;

    /**
     * @param AuditLogService $auditLogService
     */
    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * @param int $referenceId
     * @param AuditLogValueBasicEntity $auditLogValue
     * @param Identity $identity
     * @param bool $isBackend
     * @return AuditLogEntity
     */
    public function createOfferAuditLog(
        int $referenceId,
        AuditLogValueBasicEntity $auditLogValue,
        Identity $identity,
        bool $isBackend
    ): AuditLogEntity {
        $auditLog = $this->createLogEntity($auditLogValue);

        $auditLogIndex = new AuditLogIndexEntity();
        $auditLogIndex->referenceId = $referenceId;
        $auditLogIndex->referenceTable = OrderContextRepository::TABLE_NAME;

        if ($isBackend) {
            return $this->auditLogService->createBackendAuditLog($auditLog, [$auditLogIndex]);
        }

        return $this->auditLogService->createAuditLog($auditLog, $identity, [$auditLogIndex]);
    }

    /**
     * @internal
     * @param AuditLogValueBasicEntity $auditLogValue
     * @return AuditLogEntity
     */
    protected function createLogEntity(AuditLogValueBasicEntity $auditLogValue): AuditLogEntity
    {
        $auditLog = new AuditLogEntity();
        $auditLog->logValue = $auditLogValue;
        $auditLog->logType = get_class($auditLogValue);

        return $auditLog;
    }
}
