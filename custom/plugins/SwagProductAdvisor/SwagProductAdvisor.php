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

namespace SwagProductAdvisor;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use SwagProductAdvisor\Bootstrap\AclHelper;
use SwagProductAdvisor\Bootstrap\DatabaseHandler;
use SwagProductAdvisor\Bootstrap\Installer;
use SwagProductAdvisor\Bootstrap\Uninstaller;

/**
 * Class SwagProductAdvisor
 */
class SwagProductAdvisor extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context)
    {
        $dbalConnection = $this->container->get('dbal_connection');

        $dbHandler = new DatabaseHandler($dbalConnection);
        $aclHelper = new AclHelper($dbalConnection);

        $installer = new Installer(
            $dbHandler,
            $dbalConnection,
            $aclHelper
        );

        $installer->install($context);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        $dbalConnection = $this->container->get('dbal_connection');

        $dbHandler = new DatabaseHandler($dbalConnection);
        $aclHelper = new AclHelper($dbalConnection);

        $uninstaller = new Uninstaller(
            $dbHandler,
            $aclHelper
        );

        if ($context->keepUserData()) {
            $uninstaller->secureUninstall();

            return;
        }

        $uninstaller->uninstall();

        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
