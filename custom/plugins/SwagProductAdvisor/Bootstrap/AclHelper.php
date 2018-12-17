<?php

/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagProductAdvisor\Bootstrap;

use Doctrine\DBAL\Connection;

class AclHelper
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * DefaultSettingsService constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Adds the advisor ACL resource with the read-privilege. Additionally updates the menu-entry.
     *
     * @param $pluginId
     */
    public function addACLResource($pluginId)
    {
        $sql = "INSERT IGNORE INTO s_core_acl_resources (name, pluginID) VALUES ('advisor', ?);
                INSERT IGNORE INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'advisor'), 'read');";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$pluginId]);
    }

    /**
     * Deletes the advisor ACL resource and all of its privileges.
     */
    public function deleteACLResource()
    {
        $sql = "DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'advisor');
                DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'advisor');
                DELETE FROM s_core_acl_resources WHERE name = 'advisor';";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
    }
}
