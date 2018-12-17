<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493887695OrderChangeQueueAndTrigger implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493887695;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            CREATE TABLE `b2b_s_order_change_queue` (
                `s_order_id` INT(11) NOT NULL,
                `updated_at` DATETIME NOT NULL,
                `request_uuid` VARCHAR(255) NULL
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB;
        ');

        $connection->exec('
            CREATE TRIGGER `b2b_s_order_change_queue_writer` AFTER UPDATE ON `s_order` FOR EACH ROW BEGIN
                INSERT INTO `b2b_s_order_change_queue` (`s_order_id`, `updated_at`, `request_uuid`) VALUES (NEW.id, NOW(), @b2b_request_uuid);
            END
        ');

        $connection->exec('
            ALTER TABLE `b2b_budget_transaction`
                DROP FOREIGN KEY `FK_b2b_budget_transaction_b2b_store_front_auth`;
        ');

        $connection->exec('
            ALTER TABLE `b2b_budget_transaction`
                ADD COLUMN `order_context_id` INT(11) NULL DEFAULT NULL,
                ADD CONSTRAINT `FK_b2b_budget_transaction_b2b_order_context` FOREIGN KEY (`order_context_id`) 
                  REFERENCES `b2b_order_context` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL,
                ADD CONSTRAINT `FK_b2b_budget_transaction_b2b_store_front_auth` FOREIGN KEY (`auth_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL,
                ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT \'1\',
                ADD UNIQUE INDEX `b2b_budget_transaction_order_context_id_unique_IDX` (`order_context_id`);
        ');
    }

    public function updateThroughServices(Container $container)
    {
    }
}
