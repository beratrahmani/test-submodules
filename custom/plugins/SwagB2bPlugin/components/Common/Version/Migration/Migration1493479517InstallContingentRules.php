<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493479517InstallContingentRules implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493479517;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec(
            'CREATE TABLE b2b_contingent_group_rule (
              id INT(11) NOT NULL AUTO_INCREMENT,
              contingent_group_id INT(11) NOT NULL,
              type VARCHAR(255) NULL COLLATE \'utf8_unicode_ci\',
              
              PRIMARY KEY (id),
              
              CONSTRAINT b2b_contingent_group_rule_contingent_group_id_FK FOREIGN KEY (`contingent_group_id`) 
                REFERENCES `b2b_contingent_group` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE              
            )
              COLLATE=\'utf8_unicode_ci\''
        );

        $connection->exec(
            'CREATE TABLE b2b_contingent_group_rule_time_restriction (
              contingent_rule_id INT(11) NOT NULL,
              time_restriction VARCHAR(25) NULL COLLATE \'utf8_unicode_ci\',
              value DECIMAL(11,2),
              
              PRIMARY KEY (`contingent_rule_id`),
              
              CONSTRAINT b2b_contingent_group_rule_contingent_rule_id_FK FOREIGN KEY (`contingent_rule_id`) 
                REFERENCES `b2b_contingent_group_rule` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE              
            )
              COLLATE=\'utf8_unicode_ci\''
        );

        $connection->exec(
            'CREATE TABLE b2b_contingent_group_rule_category (
              contingent_rule_id INT(11) NOT NULL,
              category_id INT(11) UNSIGNED NOT NULL,
              
              PRIMARY KEY (`contingent_rule_id`),
            
              CONSTRAINT b2b_contingent_group_rule_category_contingent_rule_id_FK FOREIGN KEY (`contingent_rule_id`) 
                REFERENCES `b2b_contingent_group_rule` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,              
              CONSTRAINT b2b_contingent_group_rule_category_category_id_FK FOREIGN KEY (`category_id`) 
                REFERENCES `s_categories` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE              
            )
              COLLATE=\'utf8_unicode_ci\''
        );

        $connection->exec(
            'CREATE TABLE b2b_contingent_group_rule_product_price (
              contingent_rule_id INT(11) NOT NULL,
              product_price INT(11) NOT NULL,
            
              PRIMARY KEY (`contingent_rule_id`),
            
              CONSTRAINT b2b_contingent_group_rule_product_price_contingent_rule_id_FK FOREIGN KEY (`contingent_rule_id`)
              REFERENCES `b2b_contingent_group_rule` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
              COLLATE=\'utf8_unicode_ci\''
        );

        $connection->exec(
            'CREATE TABLE b2b_contingent_group_rule_product_order_number (
              contingent_rule_id INT(11) NOT NULL,
              product_order_number VARCHAR(255) NOT NULL,
            
              PRIMARY KEY (`contingent_rule_id`),
            
              CONSTRAINT b2b_contingent_group_rule_product_order_number_rule_id_FK FOREIGN KEY (`contingent_rule_id`)
              REFERENCES `b2b_contingent_group_rule` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
            )
              COLLATE=\'utf8_unicode_ci\''
        );
    }

    public function updateThroughServices(Container $container)
    {
    }
}
