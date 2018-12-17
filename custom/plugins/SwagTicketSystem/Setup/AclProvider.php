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

namespace SwagTicketSystem\Setup;

use Enlight_Components_Db_Adapter_Pdo_Mysql as DatabaseConnection;

class AclProvider
{
    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @var int
     */
    private $pluginId;

    /**
     * AclProvider constructor.
     *
     * @param DatabaseConnection $databaseConnection
     * @param int                $pluginId
     */
    public function __construct(DatabaseConnection $databaseConnection, $pluginId)
    {
        $this->databaseConnection = $databaseConnection;
        $this->pluginId = $pluginId;
    }

    /**
     * add the acl resource
     */
    public function addACLResource()
    {
        $sqlPrepend = "INSERT IGNORE INTO s_core_acl_resources (name, pluginID) VALUES ('ticket', ?);";

        $sql = "INSERT IGNORE INTO s_core_acl_privileges (resourceID, name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'create');
                INSERT IGNORE INTO s_core_acl_privileges (resourceID, name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'read');
                INSERT IGNORE INTO s_core_acl_privileges (resourceID, name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'update');
                INSERT IGNORE INTO s_core_acl_privileges (resourceID, name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'delete');
                INSERT IGNORE INTO s_core_acl_privileges (resourceID, name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'ticket'), 'configure');

                UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'ticket') WHERE name = 'ticket';
        ";

        if ($this->checkSCoreAclResource()) {
            $sql = $sqlPrepend . $sql;
        }

        $this->databaseConnection->query($sql, [$this->pluginId]);
    }

    /**
     * deletes the acl resource
     */
    public function deleteACLResource()
    {
        $sql = "DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'ticket');
                DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'ticket');
                DELETE FROM s_core_acl_resources WHERE name = 'ticket';
        ";

        $this->databaseConnection->query($sql, []);
    }

    /**
     * check for AclResources
     *
     * @return bool
     */
    private function checkSCoreAclResource()
    {
        $sql = "SELECT COUNT(*) FROM s_core_acl_resources AS T WHERE T.name = 'ticket'";

        $result = $this->databaseConnection->fetchOne($sql);

        if (!$result) {
            return true;
        }

        return false;
    }
}
