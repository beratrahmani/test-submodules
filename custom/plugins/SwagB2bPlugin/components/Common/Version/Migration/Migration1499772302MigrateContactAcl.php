<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1499772302MigrateContactAcl implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1499772302;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $contactIds = $connection
            ->fetchAll('SELECT id FROM b2b_debtor_contact');

        foreach ($contactIds as $contactId) {
            $connection->insert(
                'b2b_acl_contact_contact',
                [
                    'entity_id' => $contactId['id'],
                    'referenced_entity_id' => $contactId['id'],
                    'grantable' => 1,
                ]
            );
        }
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
