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
use Shopware\Components\Emotion\ComponentInstaller;
use Shopware\Components\HttpCache\CacheRouteInstaller;
use Shopware\Components\Model\ModelManager;

class Installer
{
    /**
     * @var string
     */
    private $pluginName;

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
     * @var ComponentInstaller
     */
    private $componentInstaller;

    /**
     * Installer constructor.
     *
     * @param string              $pluginName
     * @param CacheRouteInstaller $cacheRouteInstaller
     * @param CrudService         $crudService
     * @param ModelManager        $modelManager
     * @param ComponentInstaller  $componentInstaller
     */
    public function __construct(
        $pluginName,
        CacheRouteInstaller $cacheRouteInstaller,
        CrudService $crudService,
        ModelManager $modelManager,
        ComponentInstaller $componentInstaller
    ) {
        $this->pluginName = $pluginName;
        $this->cacheRouteInstaller = $cacheRouteInstaller;
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
        $this->componentInstaller = $componentInstaller;
    }

    public function install()
    {
        $this->createCacheRoute();
        $this->createModelAttributes();
        $this->createSideViewElement();
    }

    private function createCacheRoute()
    {
        $this->cacheRouteInstaller->addHttpCacheRoute('widgets/swag_emotion_advanced', 14400, ['price']);
    }

    /**
     * Creates custom model fields
     */
    private function createModelAttributes()
    {
        $attributes = AttributeDataProvider::getEmotionAdvancedAttributes();

        foreach ($attributes as $attribute) {
            $this->crudService->update(
                $attribute['table'],
                $attribute['column'],
                $attribute['type'],
                [],
                null,
                false,
                $attribute['default']
            );
        }

        $this->modelManager->generateAttributeModels(['s_emotion_attributes']);
    }

    /**
     * creates the new side view emotion element with its fields
     */
    private function createSideViewElement()
    {
        $component = $this->componentInstaller->createOrUpdate(
            $this->pluginName,
            'Sideview-Element',
            [
                'name' => 'Sideview-Element',
                'xtype' => 'emotion-sideview-widget',
                'template' => 'component_sideview',
                'cls' => 'emotion-sideview-widget',
                'description' => 'Die Sideview bietet die Möglichkeit Banner mit einen ausfahrbaren Produkt-Listing zu erstellen.',
            ]
        );

        $creator = new EmotionComponentCreator();

        $creator->addComponentFields($component);
    }
}
