<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1493729594BudgetRenameAuthorToOwner implements MigrationStepInterface
{
    public function getCreationTimeStamp(): int
    {
        return 1493729594;
    }

    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE `b2b_budget`
                CHANGE COLUMN `author_id` `owner_id` INT(11) NULL DEFAULT NULL;
        ');
    }

    public function updateThroughServices(Container $container)
    {
    }
}
