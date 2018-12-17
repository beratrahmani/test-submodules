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

namespace SwagTicketSystem;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Models\Plugin\Plugin as PluginModel;
use SwagTicketSystem\Setup\Installer;
use SwagTicketSystem\Setup\Uninstaller;
use SwagTicketSystem\Setup\Updater;

class SwagTicketSystem extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context)
    {
        $installer = $this->getInstaller($context->getPlugin());

        $installer->install();

        parent::install($context);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        $unInstaller = new Uninstaller(
            $this->container->get('dbal_connection'),
            $this->container->get('db'),
            $context->getPlugin()
        );

        $unInstaller->uninstall($context->keepUserData());

        parent::uninstall($context);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context)
    {
        $updater = new Updater(
            $this->container->get('db'),
            $this->container->get('dbal_connection'),
            $this->container->get('shopware.model_config'),
            $this->container->get('shopware_media.media_service'),
            $this->getInstaller($context->getPlugin())
        );

        $updater->update($context->getCurrentVersion());

        parent::update($context);
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

    /**
     * @param PluginModel $pluginModel
     *
     * @return Installer
     */
    private function getInstaller(PluginModel $pluginModel)
    {
        return new Installer(
            $this->container->get('db'),
            $this->container->get('models'),
            $pluginModel
       );
    }
}
