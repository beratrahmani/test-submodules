<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1521119544OrderQueueCli implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1521119544;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE b2b_s_order_change_queue
            MODIFY COLUMN `updated_at` TIMESTAMP(6);
            
            DROP TRIGGER IF EXISTS `b2b_s_order_change_queue_writer`;
            
            CREATE TRIGGER `b2b_s_order_change_queue_writer` AFTER UPDATE ON `s_order` FOR EACH ROW BEGIN
                INSERT INTO `b2b_s_order_change_queue` (`s_order_id`, `updated_at`, `request_uuid`) VALUES (NEW.id, NOW(6), @b2b_request_uuid);
            END
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
