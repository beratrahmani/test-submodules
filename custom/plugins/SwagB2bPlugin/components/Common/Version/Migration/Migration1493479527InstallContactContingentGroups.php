<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479527InstallContactContingentGroups implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479527;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_contact_contingent_group` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `contact_id` INT(11) NOT NULL,
                `contingent_group_id` INT(11) NOT NULL ,
                
                PRIMARY KEY (`id`),
                UNIQUE INDEX `b2b_contingent_id_b2b_contact_id_idx` (`contact_id`, `contingent_group_id`),
                
                CONSTRAINT `FK_b2b_contact_id` FOREIGN KEY (`contact_id`) 
                  REFERENCES `b2b_debtor_contact` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
                CONSTRAINT `FK_b2b_contingent_group_id` FOREIGN KEY (`contingent_group_id`) 
                  REFERENCES `b2b_contingent_group` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
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
