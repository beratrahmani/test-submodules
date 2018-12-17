<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1498468962CurrencyIntegration implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1498468962;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            SET FOREIGN_KEY_CHECKS=0;
            ALTER TABLE `b2b_line_item_reference` 
              CHANGE COLUMN `list_id` `list_id` INT(11) NOT NULL;
            ALTER TABLE `b2b_order_context`
              DROP COLUMN `currency_factor`,         
              DROP INDEX `FK_b2b_line_item_list_s_order_b2b_line_item_list`,
              ADD UNIQUE INDEX `b2b_line_item_list_b2b_order_context_unique` (`list_id` ASC);
            ALTER TABLE `b2b_line_item_list`
              ADD COLUMN `currency_factor` DOUBLE NOT NULL DEFAULT 1;
            ALTER TABLE `b2b_budget`
              ADD COLUMN `currency_factor` DOUBLE NOT NULL DEFAULT 1,
              CHANGE COLUMN `amount` `amount` DOUBLE DEFAULT 0;
            ALTER TABLE `b2b_contingent_group_rule_product_price`
              ADD COLUMN `currency_factor` DOUBLE NOT NULL DEFAULT 1,
              CHANGE COLUMN `product_price` `product_price` DOUBLE NOT NULL;
            ALTER TABLE `b2b_contingent_group_rule_time_restriction`
              ADD COLUMN `currency_factor` DOUBLE NOT NULL DEFAULT 1,
              CHANGE COLUMN `value` `value` DOUBLE DEFAULT NULL;
            SET FOREIGN_KEY_CHECKS=1;
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
