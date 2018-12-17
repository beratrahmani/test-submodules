<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Version\Migration;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Common\Migration\MigrationStepInterface;
use Symfony\Component\DependencyInjection\Container;

class Migration1528360322AssignUnassignedEntitiesToNewRole implements MigrationStepInterface
{
    const NEW_ROLE_NAME = 'Unsorted';

    /**
     * {@inheritdoc}
     */
    public function getCreationTimeStamp(): int
    {
        return 1528360322;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDatabase(Connection $connection)
    {
        $connection->exec(
            'INSERT INTO b2b_role (name, context_owner_id, `left`, `right`, `level`)
                (SELECT
                 \'' . self::NEW_ROLE_NAME . '\',
                 role.context_owner_id,
                 role.`right`,
                 role.`right` + 1,
                 1
               FROM b2b_role role
               WHERE role.level = 0);
            
            UPDATE b2b_role SET `right` = `right` + 2 WHERE level = 0;

            INSERT INTO b2b_role_contact (role_id, debtor_contact_id)
              (SELECT
                 role.id,
                 contact.id
               FROM b2b_debtor_contact contact LEFT OUTER JOIN b2b_role_contact b2brc ON contact.id = b2brc.debtor_contact_id
                 INNER JOIN b2b_role role
                   ON role.level = 1 AND role.name = \'' . self::NEW_ROLE_NAME . '\' AND role.context_owner_id = contact.context_owner_id
               WHERE b2brc.id IS NULL);

           INSERT INTO b2b_role_contingent_group (role_id, contingent_group_id)
              (SELECT
                 role.id,
                 contingent.id
               FROM b2b_contingent_group contingent LEFT OUTER JOIN b2b_role_contingent_group b2brc ON contingent.id = b2brc.contingent_group_id
                 INNER JOIN b2b_role role
                   ON role.level = 1 AND role.name = \'' . self::NEW_ROLE_NAME . '\' AND role.context_owner_id = contingent.context_owner_id
               WHERE b2brc.id IS NULL);

           INSERT INTO b2b_acl_role_contingent_group (entity_id, referenced_entity_id)
              (SELECT
                 role.id,
                 contingent.id
               FROM b2b_contingent_group contingent LEFT OUTER JOIN b2b_acl_role_contingent_group b2brc ON contingent.id = b2brc.referenced_entity_id
                 INNER JOIN b2b_role role
                   ON role.level = 1 AND role.name = \'' . self::NEW_ROLE_NAME . '\' AND role.context_owner_id = contingent.context_owner_id
               WHERE b2brc.id IS NULL);

           INSERT INTO b2b_acl_role_budget (entity_id, referenced_entity_id)
              (SELECT
                 role.id,
                 budget.id
               FROM b2b_budget budget LEFT OUTER JOIN b2b_acl_role_budget b2brb ON budget.id = b2brb.referenced_entity_id
                 INNER JOIN b2b_role role
                   ON role.level = 1 AND role.name = \'' . self::NEW_ROLE_NAME . '\' AND role.context_owner_id = budget.context_owner_id
               WHERE b2brb.id IS NULL);

           INSERT INTO b2b_acl_role_address (entity_id, referenced_entity_id)
              (SELECT
                 role.id,
                 address.id
               FROM s_user_addresses address LEFT OUTER JOIN b2b_acl_role_address b2bra ON address.id = b2bra.referenced_entity_id
                INNER JOIN b2b_store_front_auth auth ON address.user_id = auth.provider_context AND auth.provider_key = \'Shopware\\\B2B\\\Debtor\\\Framework\\\DebtorRepository\'
                 INNER JOIN b2b_role role
                   ON role.level = 1 AND role.name = \'' . self::NEW_ROLE_NAME . '\' AND role.context_owner_id = auth.context_owner_id
               WHERE b2bra.id IS NULL);'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateThroughServices(Container $container)
    {
    }
}
