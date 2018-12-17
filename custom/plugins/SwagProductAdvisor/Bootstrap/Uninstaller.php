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

class Uninstaller
{
    /**
     * @var DatabaseHandler
     */
    private $databaseHandler;

    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * Uninstaller constructor.
     *
     * @param DatabaseHandler $databaseHandler
     * @param AclHelper       $aclHelper
     */
    public function __construct(DatabaseHandler $databaseHandler, AclHelper $aclHelper)
    {
        $this->databaseHandler = $databaseHandler;
        $this->aclHelper = $aclHelper;
    }

    /**
     * uninstall the Plugin
     */
    public function uninstall()
    {
        $this->databaseHandler->uninstallDatabase();
        $this->secureUninstall();
    }

    /**
     * Deletes also the ACL Resources of the plugin.
     */
    public function secureUninstall()
    {
        $this->aclHelper->deleteACLResource();
    }
}
