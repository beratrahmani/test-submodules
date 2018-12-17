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

namespace SwagEmotionAdvanced\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Components\Plugin\ConfigReader;
use Shopware_Components_Config as ShopwareConfig;
use SwagEmotionAdvanced\Services\Dependencies\DependencyProvider;
use SwagEmotionAdvanced\Services\Dependencies\DependencyProviderInterface;

class QuickViewListing implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var ShopwareConfig
     */
    private $shopwareConfig;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @param string $pluginName
     * @param ConfigReader $configReader
     * @param ShopwareConfig $shopwareConfig
     * @param DependencyProviderInterface $dependencyProvider
     */
    public function __construct(
        $pluginName,
        ConfigReader $configReader,
        ShopwareConfig $shopwareConfig,
        DependencyProviderInterface $dependencyProvider
    ) {
        $this->pluginName = $pluginName;
        $this->configReader = $configReader;
        $this->shopwareConfig = $shopwareConfig;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => ['addQuickView', 100],
            'Enlight_Controller_Action_PostDispatchSecure_Widgets' => ['addQuickView', 100],
            'Enlight_Controller_Action_PreDispatch_Widgets_Listing' => 'addQuickView',
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addQuickView(ActionEventArgs $args)
    {
        $pluginConfig = $this->configReader->getByPluginName($this->pluginName, $this->dependencyProvider->getShop());
        $additionalQuickViewMode = (int) $pluginConfig['additionalQuickViewMode'];

        if ($additionalQuickViewMode === 1) {
            return;
        }

        if ($additionalQuickViewMode === 3 && !$this->shopwareConfig->get('displayListingBuyButton')) {
            return;
        }

        $view = $args->getSubject()->View();

        $view->assign('additionalQuickViewMode', $additionalQuickViewMode);
    }
}
