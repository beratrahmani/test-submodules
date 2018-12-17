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

namespace SwagPromotion\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Enlight_Event_EventArgs as EventArgs;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\Plugin\CachedConfigReader;

class Frontend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $priceDisplaying;

    /**
     * @param string             $pluginName
     * @param CachedConfigReader $configReader
     */
    public function __construct($pluginName, CachedConfigReader $configReader)
    {
        $this->priceDisplaying = $configReader->getByPluginName($pluginName)['promotionPriceDisplaying'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Legacy_Struct_Converter_Convert_Product' => 'onConvertProductStruct',
            'Legacy_Struct_Converter_Convert_List_Product' => 'onConvertProductStruct',

            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onPostDispatchFrontendDetail',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'onPostDispatchFrontendListing',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets_Listing' => 'onPostDispatchFrontendListing',
            'Shopware_Controllers_Widgets_Listing_fetchListing_preFetch' => 'onFetchListing',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Compare' => 'onPostDispatchFrontendCompare',
        ];
    }

    /**
     * @param EventArgs $args
     */
    public function onConvertProductStruct(EventArgs $args)
    {
        $productArray = $args->getReturn();
        /** @var ListProduct $product */
        $product = $args->get('product');
        $productArray['hasNewPromotionProductPrice'] = $product->hasState('hasNewPromotionProductPrice');

        $args->setReturn($productArray);
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchFrontendDetail(ActionEventArgs $args)
    {
        if ($this->priceDisplaying === 'price') {
            $view = $args->getSubject()->View();
            $product = $view->getAssign('sArticle');

            $product = $this->setPseudoPriceFlag([$product], $view);
            $product = array_shift($product);
            $view->assign('sArticle', $product);
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchFrontendListing(ActionEventArgs $args)
    {
        if ($this->priceDisplaying === 'price') {
            $view = $args->getSubject()->View();

            $products = $view->getAssign('sArticles');
            if (!$products) {
                return;
            }

            $products = $this->setPseudoPriceFlag($products, $view);

            $view->assign('sArticles', $products);
        }
    }

    /**
     * @param EventArgs $args
     */
    public function onFetchListing(EventArgs $args)
    {
        if ($this->priceDisplaying === 'price') {
            /** @var \Enlight_View_Default $view */
            $view = $args->get('subject')->View();

            $products = $view->getAssign('sArticles');
            if (!$products) {
                return;
            }

            $products = $this->setPseudoPriceFlag($products, $view);

            $view->assign('sArticles', $products);
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchFrontendCompare(ActionEventArgs $args)
    {
        if ($this->priceDisplaying === 'price') {
            $view = $args->getSubject()->View();
            $compareList = $view->getAssign('sComparisonsList');
            if (!$compareList) {
                return;
            }

            $products = $compareList['articles'];
            $products = $this->setPseudoPriceFlag($products, $view);

            $compareList['articles'] = $products;
            $view->assign('sComparisonsList', $compareList);
        }
    }

    /**
     * @param array                 $products
     * @param \Enlight_View_Default $view
     *
     * @return array
     */
    private function setPseudoPriceFlag(array $products, \Enlight_View_Default $view)
    {
        foreach ($products as &$product) {
            if (!$product['hasNewPromotionProductPrice']) {
                continue;
            }

            $product['has_pseudoprice'] = true;
            $view->assign('promotionPriceDisplaying', $this->priceDisplaying);
        }

        return $products;
    }
}
