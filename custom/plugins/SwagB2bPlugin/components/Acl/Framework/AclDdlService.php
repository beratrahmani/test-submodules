<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Doctrine\DBAL\Connection;

/**
 * Document definition Service. Creates and Drops tables ACL tables.
 */
class AclDdlService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * Necessary for plugin install
     *
     * @return AclDdlService
     */
    public static function create(): self
    {
        return new self(Shopware()->Container()->get('dbal_connection'));
    }

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param AclTable $table
     */
    public function createTable(AclTable $table)
    {
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `{$table->getName()}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `entity_id` INT(11) NOT NULL,
                `referenced_entity_id` INT(11) NOT NULL ,
                `grantable` TINYINT(4) NOT NULL DEFAULT '0',
                UNIQUE INDEX `b2b_unique_{$table->getUid()}_idx` (`entity_id`, `referenced_entity_id`),
                INDEX `PK` (`id`),
                INDEX `b2b_acl_{$table->getUid()}_idx` (`referenced_entity_id`),
                CONSTRAINT `b2b_acl_1_{$table->getUid()}` FOREIGN KEY (`entity_id`) REFERENCES `{$table->getContextTableName()}` (`{$table->getContextPrimaryKeyField()}`) ON DELETE CASCADE,
                CONSTRAINT `b2b_acl_2_{$table->getUid()}` FOREIGN KEY (`referenced_entity_id`) REFERENCES `{$table->getSubjectTableName()}` (`{$table->getSubjectPrimaryKeyField()}`) ON DELETE CASCADE
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=7
            ;
        ");
    }

    /**
     * @param AclTable $aclTable
     */
    public function dropTable(AclTable $aclTable)
    {
        $this->connection->exec("
            DROP TABLE `{$aclTable->getName()}`;
        ");
    }
}
