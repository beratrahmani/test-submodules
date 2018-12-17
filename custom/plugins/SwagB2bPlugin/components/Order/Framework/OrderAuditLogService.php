<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogIndexEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogService;
use Shopware\B2B\AuditLog\Framework\AuditLogValueBasicEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueLineItemAddEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueLineItemRemoveEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueOrderCommentEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueOrderReferenceEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueRequestedDeliveryDateEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class OrderAuditLogService
{
    const TYPE_STATUS_CHANGE = 'OrderClearanceStatusChange';

    const TYPE_LINE_ITEM_COMMENT = 'OrderClearanceLineItemComment';

    const TYPE_LINE_ITEM_ADD = 'OrderClearanceLineItemAdd';

    const TYPE_LINE_ITEM_QUANTITY_CHANGE = 'OrderClearanceLineItemQuantityChange';

    const TYPE_LINE_ITEM_REMOVE = 'OrderClearanceLineItemRemove';

    const TYPE_COMMENT = 'OrderClearanceComment';

    const TYPE_ORDER_REFERENCE = 'OrderClearanceOrderReference';

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
     * @return AuditLogEntity
     */
    public function createStatusChangeAuditLog(
        int $referenceId,
        AuditLogValueBasicEntity $auditLogValue,
        Identity $identity
    ): AuditLogEntity {
        $auditLog = $this->createLogEntity($auditLogValue);

        $auditLogIndex = new AuditLogIndexEntity();
        $auditLogIndex->referenceId = $referenceId;
        $auditLogIndex->referenceTable = OrderContextRepository::TABLE_NAME;

        return $this->auditLogService->createAuditLog($auditLog, $identity, [$auditLogIndex]);
    }

    /**
     * @param AuditLogValueBasicEntity $auditLogValue
     * @param array $references
     * @param Identity $identity
     * @return AuditLogEntity
     */
    public function addLogFromLineItemAuditLogService(
        AuditLogValueBasicEntity $auditLogValue,
        array $references,
        Identity $identity
    ): AuditLogEntity {
        return $this->auditLogService
            ->createAuditLog($this->createLogEntity($auditLogValue), $identity, $this->createIndices($references));
    }

    /**
     * @param AuditLogValueBasicEntity $auditLogValue
     * @param array $references
     * @return AuditLogEntity
     */
    public function addBackendLogFromLineItemAuditLogService(
        AuditLogValueBasicEntity $auditLogValue,
        array $references
    ): AuditLogEntity {
        return $this->auditLogService
            ->createBackendAuditLog($this->createLogEntity($auditLogValue), $this->createIndices($references));
    }

    /**
     * @param AuditLogValueLineItemRemoveEntity $auditLogValue
     * @param Identity $identity
     * @param array $reference
     * @return AuditLogEntity
     */
    public function createOrderClearanceLineItemRemove(
        AuditLogValueLineItemRemoveEntity $auditLogValue,
        array $reference,
        Identity $identity
    ): AuditLogEntity {
        $auditLog = $this->createLogEntity($auditLogValue);

        return $this->auditLogService
            ->createAuditLog($auditLog, $identity, $this->createIndices($reference));
    }

    /**
     * @param AuditLogValueLineItemAddEntity $auditLogValue
     * @param Identity $identity
     * @param array $reference
     * @return AuditLogEntity
     */
    public function createOrderClearanceLineItemAdd(
        AuditLogValueLineItemAddEntity $auditLogValue,
        array $reference,
        Identity $identity
    ): AuditLogEntity {
        $auditLog = $this->createLogEntity($auditLogValue);

        return $this->auditLogService
            ->createAuditLog($auditLog, $identity, $this->createIndices($reference));
    }

    /**
     * @param AuditLogValueOrderCommentEntity $auditLogValue
     * @param Identity $identity
     * @param array $reference
     * @return AuditLogEntity
     */
    public function createOrderClearanceComment(
        AuditLogValueOrderCommentEntity $auditLogValue,
        array $reference,
        Identity $identity
    ): AuditLogEntity {
        $auditLog = $this->createLogEntity($auditLogValue);

        return $this->auditLogService
            ->createAuditLog($auditLog, $identity, $this->createIndices($reference));
    }

    /**
     * @param AuditLogValueOrderReferenceEntity $auditLogValue
     * @param array $reference
     * @param Identity $identity
     * @return AuditLogEntity
     */
    public function createOrderClearanceOrderReference(
        AuditLogValueOrderReferenceEntity $auditLogValue,
        array $reference,
        Identity $identity
    ): AuditLogEntity {
        $auditLog = $this->createLogEntity($auditLogValue);

        return $this->auditLogService
            ->createAuditLog($auditLog, $identity, $this->createIndices($reference));
    }

    /**
     * @param AuditLogValueRequestedDeliveryDateEntity $auditLogValue
     * @param array $reference
     * @param Identity $identity
     * @return AuditLogEntity
     */
    public function createOrderClearanceRequestedDeliveryDate(
        AuditLogValueRequestedDeliveryDateEntity $auditLogValue,
        array $reference,
        Identity $identity
    ): AuditLogEntity {
        $auditLog = $this->createLogEntity($auditLogValue);

        return $this->auditLogService
            ->createAuditLog($auditLog, $identity, $this->createIndices($reference));
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

    /**
     * @internal
     * @param array $references
     * @return array
     */
    protected function createIndices(array $references): array
    {
        $indices = [];
        foreach ($references as $tableName => $index) {
            $indices[] = $this->createIndex($tableName, $index);
        }

        return $indices;
    }

    /**
     * @internal
     * @param string $tableName
     * @param int $id
     * @return AuditLogIndexEntity
     */
    protected function createIndex(string $tableName, int $id)
    {
        $auditLogIndex = new AuditLogIndexEntity();
        $auditLogIndex->referenceId = $id;
        $auditLogIndex->referenceTable = $tableName;

        return $auditLogIndex;
    }
}
