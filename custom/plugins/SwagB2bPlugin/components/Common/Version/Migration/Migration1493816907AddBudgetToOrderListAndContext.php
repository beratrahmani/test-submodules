<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493816907AddBudgetToOrderListAndContext implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493816907;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE `b2b_order_list`
                ADD COLUMN `budget_id` INT NULL AFTER `context_owner_id`,
                ADD CONSTRAINT `FK_b2b_order_list_b2b_budget` FOREIGN KEY (`budget_id`) 
                  REFERENCES `b2b_budget` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL;
        ');

        $connection->exec('
            ALTER TABLE `b2b_order_context`
                ADD COLUMN `budget_id` INT(11) NULL DEFAULT NULL AFTER `cleared_at`,
                ADD CONSTRAINT `FK_b2b_order_context_b2b_budget` FOREIGN KEY (`budget_id`) 
                  REFERENCES `b2b_budget` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL;
        ');
    }

    public function updateThroughServices(Container $container)
    {
    }
}
