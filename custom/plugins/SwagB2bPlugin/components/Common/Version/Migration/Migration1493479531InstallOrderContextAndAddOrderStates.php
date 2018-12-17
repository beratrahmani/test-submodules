<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479531InstallOrderContextAndAddOrderStates implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479531;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->query('
            CREATE TABLE `b2b_order_context` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `list_id` INT(11) NOT NULL,

                `ordernumber` VARCHAR(255) NULL DEFAULT NULL, 

                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `cleared_at` DATETIME NULL DEFAULT NULL,
                `declined_at` DATETIME NULL DEFAULT NULL,

                `shipping_address_id` INT(11) NOT NULL,
                `billing_address_id` INT(11) NOT NULL,

                `payment_id` INT(11) NULL DEFAULT NULL,
                `shipping_id` INT(11) NOT NULL,

                `status_id` VARCHAR(255) NOT NULL DEFAULT \' \',

                `comment` TEXT NOT NULL,
                `device_type` VARCHAR(255) NOT NULL,

                `order_reference` VARCHAR(255) NULL DEFAULT NULL,
                `requested_delivery_date` VARCHAR(255) NULL DEFAULT NULL,
                `auth_id` INT(11) NULL DEFAULT NULL,
                
                `currency_factor` DOUBLE NOT NULL,

                PRIMARY KEY (`id`),
                INDEX `FK_b2b_line_item_list_s_order_b2b_line_item_list` (`list_id`),
                
                CONSTRAINT `b2b_order_context_auth_user_id_FK` FOREIGN KEY (`auth_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
                CONSTRAINT `FK_b2b_order_s_order_b2b_line_item_list_id` FOREIGN KEY (`list_id`) 
                  REFERENCES `b2b_line_item_list` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            ;
        ');

        $connection->query('
            REPLACE INTO s_core_states (id, name, description, position, `group`, mail)
            VALUES (-3, \'orderclearance_denied\', \'Freigabe abgelehnt\', 100, \'state\', 0),
                   (-2, \'orderclearance_open\', \'Freigabe offen\', 99, \'state\', 0);
        ');
    }

    public function updateThroughServices(Container $container)
    {
        $attributeService = $container->get('shopware_attribute.crud_service');
        $attributeService->update('s_order_attributes', 'b2b_auth_id', 'integer');
        $attributeService->update('s_order_attributes', 'b2b_order_reference', 'string');
        $attributeService->update('s_order_attributes', 'b2b_requested_delivery_date', 'string');
        $attributeService->update('s_order_attributes', 'b2b_clearance_comment', 'string');
    }
}
