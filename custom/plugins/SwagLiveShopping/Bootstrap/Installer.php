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

namespace SwagLiveShopping\Bootstrap;

use Shopware\Components\Plugin\Context\InstallContext;
use SwagLiveShopping\Bootstrap\Exception\DatabaseSetupException;

class Installer extends AbstractSetupService
{
    /**
     * @param InstallContext $context
     *
     * @throws DatabaseSetupException
     * @throws \Exception
     */
    public function install(InstallContext $context)
    {
        $this->databaseSetup->installCustomFacet();

        try {
            $this->databaseSetup->installDatabase();
        } catch (\Exception $e) {
            throw new DatabaseSetupException('Could not update live shopping database tables. Please check your database for inconsistence data and structure');
        }

        try {
            $this->pluginElementSetup->createEmotionWidget($context->getPlugin()->getName());
            $this->databaseSetup->createAttributes();
        } catch (\Exception $e) {
            $this->databaseSetup->removeAttributes();

            throw $e;
        }
    }
}
