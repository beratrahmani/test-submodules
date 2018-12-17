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

use Doctrine\DBAL\Connection;
use Enlight_Components_Db_Adapter_Pdo_Mysql as DatabaseConnection;
use Shopware\Models\Plugin\Plugin;

class Uninstaller
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @var Plugin
     */
    private $pluginModel;

    /**
     * @param Connection         $dbalConnection
     * @param DatabaseConnection $databaseConnection
     * @param Plugin             $pluginModel
     */
    public function __construct(Connection $dbalConnection, DatabaseConnection $databaseConnection, Plugin $pluginModel)
    {
        $this->dbalConnection = $dbalConnection;
        $this->databaseConnection = $databaseConnection;
        $this->pluginModel = $pluginModel;
    }

    /**
     * @param bool $keepUserData
     */
    public function uninstall($keepUserData)
    {
        if ($keepUserData) {
            return;
        }

        $tables = [
            's_ticket_support_status',
            's_ticket_support_history',
            's_ticket_support_files',
            's_ticket_support',
            's_ticket_support_mails',
            's_ticket_support_types',
        ];

        foreach ($tables as $table) {
            $sql = 'DROP TABLE IF EXISTS ' . $table;
            $this->dbalConnection->executeQuery($sql)->execute();
        }

        $aclProvider = new AclProvider(
            $this->databaseConnection,
            $this->pluginModel->getId()
        );

        $aclProvider->deleteACLResource();
    }
}
