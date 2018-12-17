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

use Shopware\Components\Plugin\Context\UninstallContext;

class Uninstaller extends AbstractSetupService
{
    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        $this->databaseSetup->dropTables();
        $this->databaseSetup->removeAttributes();
        $this->databaseSetup->uninstallCustomFacet();

        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }

    /**
     * @param UninstallContext $context
     */
    public function uninstallSecure(UninstallContext $context)
    {
        $this->databaseSetup->removeAttributes();
        $this->databaseSetup->setCustomFacetActiveFlag(false);

        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }
}
