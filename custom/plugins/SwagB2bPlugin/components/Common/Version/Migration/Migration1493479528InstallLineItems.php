<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479528InstallLineItems implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479528;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->query('
            CREATE TABLE `b2b_line_item_list` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `context_owner_id` INT(11) NOT NULL,
                
                `amount_net`  VARCHAR(255) NULL,
                `amount`  VARCHAR(255) NULL,
                                
                PRIMARY KEY (`id`),
                INDEX `FK_b2b_line_item_list_context_owner_id` (`context_owner_id`),
                
                CONSTRAINT `b2b_line_item_list_auth_owner_id_FK` FOREIGN KEY (`context_owner_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            ;
        ');

        $connection->query('
            CREATE TABLE `b2b_line_item_reference` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `reference_number` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
                `quantity` INT(11) NOT NULL DEFAULT 1,
                `comment` LONGTEXT NOT NULL DEFAULT \'\' COLLATE \'utf8_unicode_ci\',
                `list_id` INT(11) NULL DEFAULT NULL,
                `mode` INT(11) NOT NULL,
                
                `amount_net`  VARCHAR(255) NULL,
                `amount`  VARCHAR(255) NULL,
                
                PRIMARY KEY (`id`),
                UNIQUE INDEX `b2b_line_item_reference_number_b2b_line_item_list_id_idx` (`reference_number`, `list_id`),
                INDEX `b2b_line_item_reference_number_idx` (`reference_number`),
                INDEX `b2b_line_item_list_id_idx` (`list_id`),
                
                CONSTRAINT `FK__b2b_line_item_list` FOREIGN KEY (`list_id`) 
                  REFERENCES `b2b_line_item_list` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            ;
        ');
    }

    public function updateThroughServices(Container $container)
    {
    }
}
