<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1501234236AddOffer implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1501234236;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_offer` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,

                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `expired_at` DATETIME NULL DEFAULT NULL,
                `changed_user_at` DATETIME NULL DEFAULT NULL,
                `changed_admin_at` DATETIME NULL DEFAULT NULL,
                `changed_status_at` DATETIME NULL DEFAULT NULL,
                `accepted_user_at` DATETIME NULL DEFAULT NULL,
                `accepted_admin_at` DATETIME NULL DEFAULT NULL,
                `converted_at` DATETIME NULL DEFAULT NULL,
                
                `email` VARCHAR(70) NOT NULL COLLATE \'utf8_unicode_ci\',
                `debtor_email` VARCHAR(70) NOT NULL COLLATE \'utf8_unicode_ci\',

                `declined_user_at` DATETIME NULL DEFAULT NULL,
                `declined_admin_at` DATETIME NULL DEFAULT NULL,
                
                `discount_amount_net` DOUBLE DEFAULT 0,
                `discount_amount` DOUBLE DEFAULT 0,
                
                `order_context_id` INT(11) DEFAULT NULL,                 
                `auth_id` INT(11) NOT NULL,
                `list_id` INT(11) NOT NULL,
                
                `currency_factor` DOUBLE NOT NULL,

                PRIMARY KEY (`id`),
                INDEX `b2b_offer_auth_id_idx` (`auth_id`),
                INDEX `b2b_offer_list_id_idx` (`list_id`),
                INDEX `b2b_offer_order_context_id_idx` (`order_context_id`),
                
                CONSTRAINT `b2b_offer_auth_user_id_FK` FOREIGN KEY (`auth_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON UPDATE NO ACTION ON DELETE No ACTION ,
                CONSTRAINT `FK_b2b_offer_b2b_order_context_id` FOREIGN KEY (`order_context_id`) 
                  REFERENCES `b2b_order_context` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
                CONSTRAINT `FK_b2b_offer_b2b_list_id` FOREIGN KEY (`list_id`) 
                  REFERENCES `b2b_line_item_list` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
            ;
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
