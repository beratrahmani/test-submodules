<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclDdlService;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479514InstallRole implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479514;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_role` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(70) NOT NULL COLLATE \'utf8_unicode_ci\',
                `context_owner_id` INT(11) NOT NULL,
                
                PRIMARY KEY (`id`),
                INDEX `FK_b2b_role_context_owner_id` (`context_owner_id`),
                
                CONSTRAINT `b2b_role_auth_owner_id_FK` FOREIGN KEY (`context_owner_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            AUTO_INCREMENT=100;
        ');
    }

    public function updateThroughServices(Container $container)
    {
        foreach (RoleFrameworkConfiguration::createAclTables() as $table) {
            AclDdlService::create()->createTable($table);
        }
    }
}
