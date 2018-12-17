<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1502802189ChangeAmountType implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1502802189;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE b2b_line_item_reference MODIFY amount DOUBLE;
        ');

        $connection->exec('
            ALTER TABLE b2b_line_item_reference MODIFY amount_net DOUBLE;
        ');

        $connection->exec('
            ALTER TABLE b2b_line_item_list MODIFY amount_net DOUBLE;
        ');

        $connection->exec('
            ALTER TABLE b2b_line_item_list MODIFY amount_net DOUBLE;
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
