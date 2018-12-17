<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclDdlService;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Symfony\Component\DependencyInjection\Container;

class Migration1501141769UpdateRoleAclTables implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1501141769;
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
        foreach (RoleFrameworkConfiguration::createRoleAclTables() as $table) {
            AclDdlService::create()->createTable($table);
        }

        $connection = $container->get('dbal_connection');

        $roleIds = $connection->createQueryBuilder()
            ->select('id')
            ->from('b2b_role')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($roleIds as $roleId) {
            $connection->insert(
                'b2b_acl_role_role',
                [
                    'entity_id' => $roleId,
                    'referenced_entity_id' => $roleId,
                    'grantable' => 1,
                ]
            );
        }
    }
}
