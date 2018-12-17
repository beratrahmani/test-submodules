<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1514358891AccountPage implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1514358891;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE `b2b_store_front_auth`
            ADD COLUMN avatar VARCHAR(255) DEFAULT "" NOT NULL
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
