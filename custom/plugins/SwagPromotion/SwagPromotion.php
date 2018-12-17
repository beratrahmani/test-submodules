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

namespace SwagPromotion;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use SwagPromotion\Bootstrap\AttributesHandler;
use SwagPromotion\Bootstrap\DatabaseHandler;
use SwagPromotion\Bootstrap\UpdateHandler;

class SwagPromotion extends Plugin
{
    /**
     * @param InstallContext $installContext
     */
    public function install(InstallContext $installContext)
    {
        $databaseHandler = new DatabaseHandler(
            $this->container->get('dbal_connection')
        );

        $attributesHandler = new AttributesHandler(
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('models')
        );

        $databaseHandler->installTables();
        $attributesHandler->installAttributes();
    }

    /**
     * @param UpdateContext $updateContext
     */
    public function update(UpdateContext $updateContext)
    {
        $updater = new UpdateHandler(
            $this->container->get('dbal_connection'),
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service')
        );

        $updater->update($updateContext->getCurrentVersion());

        // clear cache
        $updateContext->scheduleClearCache(UpdateContext::CACHE_LIST_ALL);
    }

    /**
     * @param UninstallContext $uninstallContext
     */
    public function uninstall(UninstallContext $uninstallContext)
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $databaseHandler = new DatabaseHandler(
            $this->container->get('dbal_connection')
        );

        $attributesHandler = new AttributesHandler(
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('models')
        );

        $attributesHandler->uninstallAttributes();
        $databaseHandler->uninstallTables();

        $uninstallContext->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }

    /**
     * @param ActivateContext $activateContext
     */
    public function activate(ActivateContext $activateContext)
    {
        $activateContext->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * @param DeactivateContext $deactivateContext
     */
    public function deactivate(DeactivateContext $deactivateContext)
    {
        $deactivateContext->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);
    }
}
