<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479512InstallAuditLog implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479512;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec(
            '
            CREATE TABLE `b2b_audit_log_author` (
                `hash` VARCHAR(32) NOT NULL COLLATE \'utf8_unicode_ci\',
                `salutation` VARCHAR(30) COLLATE \'utf8_unicode_ci\', 
                `title` VARCHAR(100) COLLATE \'utf8_unicode_ci\', 
                `firstname` VARCHAR(100) COLLATE \'utf8_unicode_ci\', 
                `lastname` VARCHAR(100) COLLATE \'utf8_unicode_ci\',
                `email` VARCHAR(70) NOT NULL COLLATE \'utf8_unicode_ci\',
                `is_api` TINYINT(1) NOT NULL,
                
                PRIMARY KEY (`hash`)
            )
        '
        );

        $connection->exec(
            '
            CREATE TABLE `b2b_audit_log` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
              `log_value` TEXT COLLATE \'utf8_unicode_ci\',
              `log_type` VARCHAR(1024) NOT NULL COLLATE \'utf8_unicode_ci\',
              `event_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
              `author_hash` VARCHAR(32) NOT NULL COLLATE \'utf8_unicode_ci\',
                          
              PRIMARY KEY (`id`),              
              
              CONSTRAINT `FK_b2b_audit_log_author_hash` FOREIGN KEY (`author_hash`)
              REFERENCES `b2b_audit_log_author` (`hash`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB;
        '
        );

        $connection->exec(
            '
            CREATE TABLE `b2b_audit_log_index` (              
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
              `audit_log_id` INT(11) UNSIGNED NOT NULL,
              `reference_table` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
              `reference_id` INT(11) NOT NULL,
                          
              PRIMARY KEY (`id`),
              INDEX `I_b2b_audit_log_table_id` (`reference_table`, `reference_id`),
              
              CONSTRAINT `FK_b2b_audit_log_id` FOREIGN KEY (`audit_log_id`)
              REFERENCES `b2b_audit_log` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB;
        '
        );
    }

    public function updateThroughServices(Container $container)
    {
    }
}
