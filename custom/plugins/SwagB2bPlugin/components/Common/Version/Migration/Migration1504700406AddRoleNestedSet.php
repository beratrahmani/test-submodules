<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1504700406AddRoleNestedSet implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1499922631;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE `b2b_role`
                ADD COLUMN `left` INT(11) NULL,
                ADD COLUMN `right` INT(11) NULL,
                ADD COLUMN `level` INT(11) NULL
           ;
        ');

        $this->buildRoleTrees($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function updateThroughServices(Container $container)
    {
    }

    /**
     * @param Connection $connection
     */
    public function buildRoleTrees(Connection $connection)
    {
        $contextOwners = $connection->createQueryBuilder()->select('DISTINCT context_owner_id')
            ->from('b2b_role')
            ->orderBy('context_owner_id')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($contextOwners as $contextOwner) {
            $connection->createQueryBuilder()
                ->insert('b2b_role')
                ->values(
                    [
                        'name' => ':name',
                        'context_owner_id' => ':contextOwner',
                        '`left`' => 1,
                        '`level`' => 0,
                        '`right`' => 2,
                    ]
                )
                ->setParameter('name', 'root')
                ->setParameter('contextOwner', (int) $contextOwner)
                ->execute();

            $roles = $connection->createQueryBuilder()
                ->select('*')
                ->from('b2b_role')
                ->where('context_owner_id = :contextOwner')
                ->andWhere('`level` is null or `left` is null or `right` is null')
                ->orderBy('id')
                ->setParameter('contextOwner', (int) $contextOwner)
                ->execute()
                ->fetchAll();
            $counter = 2;
            foreach ($roles as $role) {
                $role['`left`'] = $counter;
                $role['`right`'] = $counter + 1;
                $role['`level`'] = 1;
                unset($role['level'], $role['left'], $role['right']);
                $connection->update('b2b_role', $role, ['id' => $role['id']]);

                $counter += 2;
            }

            $connection->createQueryBuilder()
                ->update('b2b_role')
                ->set('`right`', ':counter')
                ->where('context_owner_id = :contextOwner')
                ->andWhere('`level` = 0')
                ->andWhere('`left` = 1')
                ->setParameter('counter', $counter)
                ->setParameter('contextOwner', $contextOwner)
                ->execute();
        }
    }
}
