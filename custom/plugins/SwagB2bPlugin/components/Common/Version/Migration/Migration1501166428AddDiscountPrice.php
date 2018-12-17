<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1501166428AddDiscountPrice implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1501166428;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE b2b_line_item_reference
            ADD discount_amount double;
        ');

        $connection->exec('
            ALTER TABLE b2b_line_item_reference
            ADD discount_amount_net double;
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
