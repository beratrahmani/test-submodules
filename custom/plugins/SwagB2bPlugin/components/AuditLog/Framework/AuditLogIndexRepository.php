<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;

class AuditLogIndexRepository
{
    const TABLE_NAME = 'b2b_audit_log_index';

    const TABLE_ALIAS = 'auditLogIndex';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @param AuditLogIndexEntity $auditLogIndexEntity
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     * @return AuditLogIndexEntity
     */
    public function createAuditLogIndex(AuditLogIndexEntity $auditLogIndexEntity): AuditLogIndexEntity
    {
        if (!$auditLogIndexEntity->isNew()) {
            throw new CanNotInsertExistingRecordException('The Audit Log Index provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            $auditLogIndexEntity->toDatabaseArray()
        );

        $auditLogIndexEntity->id = (int) $this->connection->lastInsertId();

        return $auditLogIndexEntity;
    }
}
