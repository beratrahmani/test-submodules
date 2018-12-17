<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479526InstallRoleContingentGroups implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479526;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec(
            'CREATE TABLE `b2b_role_contingent_group` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `role_id` INT(11) NOT NULL,
              `contingent_group_id` INT(11) NOT NULL,
              
              PRIMARY KEY (`id`),
              UNIQUE INDEX `b2b_role_id_b2b_contingent_group_id_idx` (`role_id`, `contingent_group_id`),
              INDEX `FK_rcg_b2b_contingent_group_id` (`contingent_group_id`),
              INDEX `FK_rcg_b2b_role_id` (`role_id`),
              
              CONSTRAINT `FK_rcg_b2b_contingent_group_id` FOREIGN KEY (`contingent_group_id`)
                REFERENCES `b2b_contingent_group` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
              CONSTRAINT `FK_rcg_b2b_role_contingent_group_id` FOREIGN KEY (`role_id`)
                REFERENCES `b2b_role` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
              COLLATE=\'utf8_unicode_ci\'
              ENGINE=InnoDB;'
        );
    }

    public function updateThroughServices(Container $container)
    {
    }
}
