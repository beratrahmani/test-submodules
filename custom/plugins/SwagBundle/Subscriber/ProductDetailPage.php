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

namespace SwagBundle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\Plugin\ConfigReader;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Services\Dependencies\ProviderInterface;
use SwagBundle\Services\Listing\BundleServiceInterface;

class ProductDetailPage implements SubscriberInterface
{
    /**
     * @var BundleServiceInterface
     */
    private $bundleService;

    /**
     * @var BundleComponentInterface
     */
    private $bundleComponent;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var ProviderInterface
     */
    private $dependencyProvider;

    /**
     * @param BundleServiceInterface   $bundleService
     * @param BundleComponentInterface $bundleComponent
     * @param ConfigReader             $configReader
     * @param string                   $pluginName
     * @param ProviderInterface        $dependencyProvider
     */
    public function __construct(
        BundleServiceInterface $bundleService,
        BundleComponentInterface $bundleComponent,
        ConfigReader $configReader,
        $pluginName,
        ProviderInterface $dependencyProvider
    ) {
        $this->bundleService = $bundleService;
        $this->bundleComponent = $bundleComponent;
        $this->configReader = $configReader;
        $this->pluginName = $pluginName;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onProductDetailPage',
        ];
    }

    /**
     * Event listener function of the product detail page.
     * Assigns all bundles for this product to the view
     *
     * @param ActionEventArgs $arguments
     */
    public function onProductDetailPage(ActionEventArgs $arguments)
    {
        /** @var \Enlight_Controller_Request_RequestHttp $request */
        $request = $arguments->getSubject()->Request();

        /** @var \Enlight_View_Default $view */
        $view = $arguments->getSubject()->View();

        $productId = (int) $request->getParam('sArticle');
        $productNumber = $view->getAssign('sArticle')['ordernumber'];

        $view->assign('bundleMessage', $request->get('bundleMessage'));

        // if product is loaded in QuickView request only needed bundle information
        if ($request->getParam('isEmotionAdvancedQuickView', false)) {
            $view->assign('swagBundleIsEmotionAdvancedQuickView', true);

            $product = new ListProduct($productId, null, null);
            $bundles = $this->bundleService->getListOfBundles([$product]);
            $view->assign('sBundles', $bundles);

            return;
        }

        //converts the configurator selection to the bundle structure.
        $bundles = $this->bundleComponent->getBundlesForDetailPage($productId, $productNumber);

        if (is_array($bundles)) {
            $view->assign('sBundles', $bundles);
        } else {
            $view->assign('sBundlesButNotForThisVariant', $bundles);
        }

        $pluginConfig = $this->configReader->getByPluginName($this->pluginName, $this->dependencyProvider->getShop());

        $view->assign('sShowBundleBelowDesc', $pluginConfig['SwagBundleShowBelowDesc']);
    }
}
