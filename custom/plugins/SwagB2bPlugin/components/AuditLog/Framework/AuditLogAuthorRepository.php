<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

use Doctrine\DBAL\Connection;

class AuditLogAuthorRepository
{
    const TABLE_NAME = 'b2b_audit_log_author';

    const TABLE_ALIAS = 'auditLogAuthor';

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
     * @param AuditLogAuthorEntity $auditLogAuthorEntity
     * @return AuditLogAuthorEntity
     */
    public function createAuditLogAuthor(AuditLogAuthorEntity $auditLogAuthorEntity): AuditLogAuthorEntity
    {
        $data = $auditLogAuthorEntity
            ->toDatabaseArray();

        $this->connection->executeUpdate(
            'INSERT INTO ' . self::TABLE_NAME . ' (' . implode(', ', array_keys($data)) . ') ' .
            'VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ') ON DUPLICATE KEY UPDATE `hash`=`hash`',
            array_values($data)
        );

        return $auditLogAuthorEntity;
    }
}
