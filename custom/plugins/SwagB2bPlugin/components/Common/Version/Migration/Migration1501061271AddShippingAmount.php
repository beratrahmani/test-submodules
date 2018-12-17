<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1501061271AddShippingAmount implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1501061271;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE `b2b_order_context`
              ADD COLUMN `shipping_amount` DOUBLE NOT NULL DEFAULT 0,
              ADD COLUMN `shipping_amount_net` DOUBLE NOT NULL DEFAULT 0;
        ');

        $connection->exec('
            UPDATE `b2b_order_context`
            INNER JOIN `s_order`
            ON `b2b_order_context`.`ordernumber` = `s_order`.`ordernumber`
            SET `b2b_order_context`.`shipping_amount` = `s_order`.`invoice_shipping`, 
                `b2b_order_context`.`shipping_amount_net` = `s_order`.`invoice_shipping_net`;
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
