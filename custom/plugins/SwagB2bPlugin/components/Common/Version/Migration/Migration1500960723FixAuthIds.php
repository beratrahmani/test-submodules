<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1500960723FixAuthIds implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1500960723;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            INSERT INTO b2b_store_front_auth (provider_key, provider_context, context_owner_id)  
              SELECT 
                  "Shopware\\B2B\\Contact\\Framework\\ContactRepository" AS provider_key, 
                  contact.id AS provider_context, 
                  contact.context_owner_id AS context_owner_id 
              FROM b2b_debtor_contact contact 
              LEFT JOIN b2b_store_front_auth auth ON auth.provider_context = contact.id 
              WHERE auth.id IS NULL 
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
