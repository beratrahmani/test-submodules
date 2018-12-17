<?php declare(strict_types=1);

namespace SwagB2bPlugin;

use Shopware\B2B\AclRoute\Framework\AclRoutingUpdateService;
use Shopware\B2B\Common\Migration\MigrationCollectionLoader;
use Shopware\B2B\Common\Migration\MigrationRuntime;

class SetUp
{
    /**
     * @param string $migrationPath
     */
    public function setUp(string $migrationPath)
    {
        $this->migrate($migrationPath);
        $this->updateAclRouteConfig();
    }

    /**
     * @internal
     * @param string $migrationPath
     */
    protected function migrate(string $migrationPath)
    {
        $migrations = MigrationCollectionLoader::create()
            ->addDirectory($migrationPath, 'Shopware\B2B\Common\Version\Migration')
            ->getMigrationCollection();

        MigrationRuntime::create('b2b_migration')->migrate($migrations);
    }

    /**
     * @internal
     */
    protected function updateAclRouteConfig()
    {
        AclRoutingUpdateService::create()->addConfig(require __DIR__ . '/Resources/acl-config.php');
    }
}
