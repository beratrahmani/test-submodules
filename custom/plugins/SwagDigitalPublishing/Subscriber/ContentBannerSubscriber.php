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

namespace SwagDigitalPublishing\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Model\ModelManager;
use SwagDigitalPublishing\Components\Emotion\Preset\ComponentHandler\BannerComponentHandler;
use SwagDigitalPublishing\Components\Emotion\Preset\ComponentHandler\BannerSliderComponentHandler;

class ContentBannerSubscriber implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginBasePath;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var \Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * @param string $pluginBasePath
     * @param Container $container
     * @param MediaService $mediaService
     * @param ModelManager $modelManager
     * @param \Enlight_Template_Manager $templateManager
     */
    public function __construct(
        $pluginBasePath,
        Container $container,
        MediaService $mediaService,
        ModelManager $modelManager,
        \Enlight_Template_Manager $templateManager
    )
    {
        $this->pluginBasePath = $pluginBasePath;
        $this->container = $container;
        $this->mediaService = $mediaService;
        $this->modelManager = $modelManager;
        $this->templateManager = $templateManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Emotion_Collect_Preset_Component_Handlers' => 'registerComponentHandler',
        ];
    }

    /**
     * @return ArrayCollection
     */
    public function registerComponentHandler()
    {
        $this->templateManager->addTemplateDir($this->pluginBasePath . '/Resources/views');
        $collection = new ArrayCollection();

        $collection->add(new BannerComponentHandler($this->modelManager, $this->mediaService, $this->container));
        $collection->add(new BannerSliderComponentHandler($this->modelManager, $this->mediaService, $this->container));

        return $collection;
    }
}
