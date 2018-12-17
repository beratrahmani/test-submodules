<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1519895825AddDefaultAddressIdsToContact implements MigrationStepInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1519895825;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec('
            ALTER TABLE b2b_debtor_contact
            ADD COLUMN `default_billing_address_id` INT(11),
            ADD COLUMN `default_shipping_address_id` INT(11);
            
            UPDATE b2b_debtor_contact contact
            LEFT JOIN b2b_store_front_auth auth
              ON contact.context_owner_id = auth.id
              AND auth.provider_key = "Shopware\\B2B\\Debtor\\Framework\\DebtorRepository"
            LEFT JOIN s_user user 
              ON auth.provider_context = user.id
            SET contact.default_billing_address_id = user.default_billing_address_id,
                contact.default_shipping_address_id = user.default_shipping_address_id;
        ');
    }

    /**
    * {@inheritdoc}
    */
    public function updateThroughServices(Container $container)
    {
    }
}
