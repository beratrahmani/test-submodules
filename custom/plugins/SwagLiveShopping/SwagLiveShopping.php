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

namespace SwagLiveShopping;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use SwagLiveShopping\Bootstrap\DatabaseSetup;
use SwagLiveShopping\Bootstrap\Installer;
use SwagLiveShopping\Bootstrap\Uninstaller;
use SwagLiveShopping\Bootstrap\Updater;

class SwagLiveShopping extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context)
    {
        $installer = new Installer(
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('shopware.emotion_component_installer'),
            $this->container->get('config')->get('version')
        );

        $installer->install($context);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context)
    {
        $updater = new Updater(
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('shopware.emotion_component_installer'),
            $this->container->get('config')->get('version')
        );

        $updater->update($context);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);

        $uninstaller = new Uninstaller(
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('shopware.emotion_component_installer'),
            $this->container->get('config')->get('version')
        );

        if ($context->keepUserData()) {
            $uninstaller->uninstallSecure($context);

            return;
        }

        $uninstaller->uninstall($context);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context)
    {
        $dbSetup = new DatabaseSetup(
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service')
        );

        $dbSetup->setCustomFacetActiveFlag(true);

        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context)
    {
        $dbSetup = new DatabaseSetup(
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service')
        );

        $dbSetup->setCustomFacetActiveFlag(false);

        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
