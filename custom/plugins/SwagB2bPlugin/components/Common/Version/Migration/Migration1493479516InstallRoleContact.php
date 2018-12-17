<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479516InstallRoleContact implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479516;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_role_contact` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `role_id` INT(11) NOT NULL,
                `debtor_contact_id` INT(11) NOT NULL ,
                
                PRIMARY KEY (`id`),
                UNIQUE INDEX `b2b_role_id_b2b_debtor_contact_id_idx` (`role_id`, `debtor_contact_id`),
                INDEX `FK_b2b_debtor_contact_id` (`debtor_contact_id`),
                INDEX `FK_b2b_role_id` (`role_id`),
                
                CONSTRAINT `FK_b2b_debtor_contact_id` FOREIGN KEY (`debtor_contact_id`) 
                    REFERENCES `b2b_debtor_contact` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
                CONSTRAINT `FK_b2b_role_id` FOREIGN KEY (`role_id`) 
                    REFERENCES `b2b_role` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
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
