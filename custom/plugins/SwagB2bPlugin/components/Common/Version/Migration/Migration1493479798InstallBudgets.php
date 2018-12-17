<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclDdlService;
use Shopware\B2B\Budget\Framework\DependencyInjection\BudgetFrameworkConfiguration;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479798InstallBudgets implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479798;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->query('
            CREATE TABLE `b2b_budget` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `context_owner_id` INT(11) NOT NULL,
                
                `identifier` VARCHAR(255) NOT NULL DEFAULT \'\' COLLATE \'utf8_unicode_ci\',
                `name` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
                
                `author_id` INT(11) NULL DEFAULT NULL,
                `notify_author` TINYINT(1) NULL DEFAULT NULL,
                `notify_author_percentage` INT(11) NULL DEFAULT NULL,
                
                `active` TINYINT(1) NULL DEFAULT NULL,
                
                `amount` INT(11) NULL DEFAULT \'0\',
                `refresh_type` VARCHAR(50) NULL DEFAULT NULL COLLATE \'utf8_unicode_ci\',
                `fiscal_year` INT(11) NOT NULL DEFAULT \'1\',
            
                PRIMARY KEY (`id`),
                INDEX `FK_b2b_budget_b2b_store_front_auth` (`context_owner_id`),
                INDEX `FK_b2b_budget_b2b_store_front_auth_2` (`author_id`),
            
                CONSTRAINT `FK_b2b_budget_b2b_store_front_auth` FOREIGN KEY (`context_owner_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON DELETE CASCADE,
                CONSTRAINT `FK_b2b_budget_b2b_store_front_auth_2` FOREIGN KEY (`author_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON DELETE SET NULL
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
        ');

        $connection->query('
            CREATE TABLE `b2b_budget_transaction` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `budget_id` INT(11) NOT NULL,
                `auth_id` INT(11) NULL,
                `refresh_group` INT(11) NULL DEFAULT NULL,
                `amount` FLOAT NOT NULL,
            
                PRIMARY KEY (`id`),
                INDEX `FK_b2b_budget_transaction_b2b_budget` (`budget_id`),
                INDEX `FK_b2b_budget_transaction_b2b_store_front_auth` (`auth_id`),
            
                CONSTRAINT `FK_b2b_budget_transaction_b2b_budget` FOREIGN KEY (`budget_id`) 
                  REFERENCES `b2b_budget` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
                CONSTRAINT `FK_b2b_budget_transaction_b2b_store_front_auth` FOREIGN KEY (`auth_id`) 
                  REFERENCES `b2b_store_front_auth` (`id`) ON DELETE SET NULL
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
        ');

        $connection->query('
            CREATE TABLE `b2b_budget_address` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `budget_id` INT(11) NOT NULL,
                `address_id` INT(11) NOT NULL,
            
                PRIMARY KEY (`id`),
                INDEX `FK_b2b_budget_address_s_user_addresses` (`address_id`),
                INDEX `FK_b2b_budget_address_b2b_budget` (`budget_id`),
            
                CONSTRAINT `FK_b2b_budget_address_b2b_budget` FOREIGN KEY (`budget_id`) 
                  REFERENCES `b2b_budget` (`id`),
                CONSTRAINT `FK_b2b_budget_address_s_user_addresses` FOREIGN KEY (`address_id`) 
                  REFERENCES `s_user_addresses` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
            COLLATE=\'utf8_unicode_ci\'
            ENGINE=InnoDB
        ');
    }

    public function updateThroughServices(Container $container)
    {
        foreach (BudgetFrameworkConfiguration::createAclTables() as $table) {
            AclDdlService::create()->createTable($table);
        }
    }
}
