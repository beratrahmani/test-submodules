<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479511InstallAclRouting implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479511;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_acl_route_privilege` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `resource_name` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
                `privilege_type` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',

                PRIMARY KEY (`id`),
                UNIQUE INDEX `b2b_acl_route_privilege_resource_privilege_idx` (`resource_name`, `privilege_type`)
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            AUTO_INCREMENT=100;
        ');

        $connection->exec('
            CREATE TABLE `b2b_acl_route` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `privilege_id` INT(11) NOT NULL,
                `controller` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
                `action` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
                
                PRIMARY KEY (`id`),
                UNIQUE INDEX `b2b_acl_route_controller_action_idx` (`controller`, `action`),
                INDEX `FK_b2b_acl_route_privilege_b2b_acl_route` (`privilege_id`),
                
                CONSTRAINT `FK_b2b_acl_route_privilege_b2b_acl_route` FOREIGN KEY (`privilege_id`) 
                    REFERENCES `b2b_acl_route_privilege` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            AUTO_INCREMENT=100;
        ');
    }

    public function updateThroughServices(Container $container)
    {
    }
}
