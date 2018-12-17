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
use Shopware\Components\Plugin\Context\InstallContext;

class Installer
{
    /**
     * @var DatabaseHandler
     */
    private $databaseHandler;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * Installer constructor.
     *
     * @param DatabaseHandler $databaseHandler
     * @param Connection      $connection
     * @param AclHelper       $aclHelper
     */
    public function __construct(
        DatabaseHandler $databaseHandler,
        Connection $connection,
        AclHelper $aclHelper
    ) {
        $this->databaseHandler = $databaseHandler;
        $this->connection = $connection;
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param InstallContext $context
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function install(InstallContext $context)
    {
        $this->generateSchemas();

        $this->aclHelper->deleteACLResource();
        $this->aclHelper->addACLResource($context->getPlugin()->getId());

        return true;
    }

    /**
     * @throws \Exception
     */
    private function generateSchemas()
    {
        try {
            $this->databaseHandler->installDatabase();
        } catch (\Exception $e) {
            if (!$this->checkIfTablesExists()) {
                throw new \Exception('Product Advisor Installer.php can not create the database schema... ' . $e->getMessage());
            }
        }
    }

    /**
     * This method checks if all advisor tables are installed
     *
     * @return bool
     */
    private function checkIfTablesExists()
    {
        $allTablesAreInstalled = true;
        $tables = [
            's_plugin_product_advisor_advisor',
            's_plugin_product_advisor_answer',
            's_plugin_product_advisor_question',
            's_plugin_product_advisor_sessions',
        ];

        $sql = 'SHOW TABLES LIKE :table;';

        foreach ($tables as $table) {
            $result = $this->connection->fetchAssoc($sql, ['table' => $table]);
            if (!$result) {
                $allTablesAreInstalled = false;
            }
        }

        return $allTablesAreInstalled;
    }
}
