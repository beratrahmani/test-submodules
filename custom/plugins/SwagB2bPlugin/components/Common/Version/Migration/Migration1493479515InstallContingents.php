<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclDdlService;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\B2B\ContingentGroup\Framework\DependencyInjection\ContingentGroupFrameworkConfiguration;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479515InstallContingents implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479515;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec(
            'CREATE TABLE `b2b_contingent_group` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `context_owner_id` INT(11) NOT NULL,
              `name` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
              `description` TEXT NULL COLLATE \'utf8_unicode_ci\',
              
              PRIMARY KEY (`id`),
              
              INDEX `b2b_contingent_group_auth_owner_id_IDX` (`context_owner_id`),
              
              CONSTRAINT `b2b_contingent_group_auth_owner_id_FK` FOREIGN KEY (`context_owner_id`) 
                REFERENCES `b2b_store_front_auth` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB;'
        );
    }

    public function updateThroughServices(Container $container)
    {
        foreach (ContingentGroupFrameworkConfiguration::createAclTables() as $table) {
            AclDdlService::create()->createTable($table);
        }
    }
}
