<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclDdlService;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Symfony\Component\DependencyInjection\Container;

class Migration1499694285UpdateContactAclTables implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1499694285;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
        foreach (ContactFrameworkConfiguration::createContactAclTables() as $table) {
            AclDdlService::create()->createTable($table);
        }
    }
}
