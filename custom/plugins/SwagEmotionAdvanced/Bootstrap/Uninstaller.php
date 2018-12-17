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

namespace SwagEmotionAdvanced\Bootstrap;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\HttpCache\CacheRouteInstaller;
use Shopware\Components\Model\ModelManager;

class Uninstaller
{
    /**
     * @var CacheRouteInstaller
     */
    private $cacheRouteInstaller;

    /**
     * @var CrudService
     */
    private $crudService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * Installer constructor.
     *
     * @param CacheRouteInstaller $cacheRouteInstaller
     * @param CrudService         $crudService
     * @param ModelManager        $modelManager
     */
    public function __construct(
        CacheRouteInstaller $cacheRouteInstaller,
        CrudService $crudService,
        ModelManager $modelManager
    ) {
        $this->cacheRouteInstaller = $cacheRouteInstaller;
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
    }

    public function secureUninstall()
    {
        $this->removeCacheRoute();
    }

    public function uninstall()
    {
        $this->removeAttributes();
    }

    private function removeCacheRoute()
    {
        $this->cacheRouteInstaller->removeHttpCacheRoute('widgets/swag_emotion_advanced');
    }

    /**
     * Removes custom model fields
     */
    private function removeAttributes()
    {
        $attributes = AttributeDataProvider::getEmotionAdvancedAttributes();

        foreach ($attributes as $attribute) {
            $this->crudService->delete($attribute['table'], $attribute['column']);
        }

        $this->modelManager->generateAttributeModels(['s_emotion_attributes']);
    }
}
