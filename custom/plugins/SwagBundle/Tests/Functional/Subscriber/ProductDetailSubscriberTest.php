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

namespace SwagBundle\Tests\Functional\Subscriber;

use Enlight_Controller_ActionEventArgs;
use Enlight_Template_Manager;
use Enlight_View_Default;
use SwagBundle\Subscriber\ProductDetailPage;
use SwagBundle\Tests\DatabaseTestCaseTrait;
use SwagBundle\Tests\Functional\BundleControllerTestCase;
use SwagBundle\Tests\Functional\Mocks\FrontendControllerMock;

class ProductDetailSubscriberTest extends BundleControllerTestCase
{
    use DatabaseTestCaseTrait;

    public function test_onProductDetailPage_should_return_null_and_assign_bundles_is_quickview()
    {
        $frontendSubscriber = $this->getProductDetailSubscriber();

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/default_bundle.sql'),
            [':bundleId' => 150]
        );

        $view = new Enlight_View_Default(
            new Enlight_Template_Manager()
        );

        $frontendControllerMock = new FrontendControllerMock(
            $view,
            $this->Request()
        );

        $this->Request()->setParam('isEmotionAdvancedQuickView', true);
        $this->Request()->setParam('sArticle', 178);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs(['subject' => $frontendControllerMock]);
        $return = $frontendSubscriber->onProductDetailPage($enlightEventArgs);

        self::assertNull($return);
        self::assertTrue($view->getAssign('swagBundleIsEmotionAdvancedQuickView'));
        self::assertTrue(count($view->getAssign('sBundles')) > 0);
    }

    public function test_onProductDetailPage_should_assign_bundles_is_bundle_article()
    {
        $frontendSubscriber = $this->getProductDetailSubscriber();

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/default_bundle.sql'),
            [':bundleId' => 150]
        );

        $view = new Enlight_View_Default(
            new Enlight_Template_Manager()
        );

        $view->assign('sArticle', ['ordernumber' => 'SW10178']);

        $frontendControllerMock = new FrontendControllerMock(
            $view,
            $this->Request()
        );

        $productId = 178;

        $this->Request()->setParam('sArticle', $productId);
        $this->Request()->setParam('group', []);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs(['subject' => $frontendControllerMock]);
        $frontendSubscriber->onProductDetailPage($enlightEventArgs);

        self::assertTrue(count($view->getAssign('sBundles')) > 0);
    }

    public function test_onProductDetailPage_should_assign_bundle_but_not_for_this_variant()
    {
        $frontendSubscriber = $this->getProductDetailSubscriber();

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/default_bundle.sql'),
            [':bundleId' => 150]
        );

        $view = new Enlight_View_Default(
            new Enlight_Template_Manager()
        );

        $frontendControllerMock = new FrontendControllerMock(
            $view,
            $this->Request()
        );

        $productId = 200;

        $this->Request()->setParam('sArticle', $productId);
        $this->Request()->setParam('group', []);

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs(['subject' => $frontendControllerMock]);
        $frontendSubscriber->onProductDetailPage($enlightEventArgs);

        self::assertArrayHasKey('sBundlesButNotForThisVariant', $view->getAssign());
    }

    /**
     * @return ProductDetailPage
     */
    private function getProductDetailSubscriber()
    {
        return new ProductDetailPage(
            Shopware()->Container()->get('swag_bundle.listing.bundle_service'),
            Shopware()->Container()->get('swag_bundle.bundle_component'),
            Shopware()->Container()->get('shopware.plugin.cached_config_reader'),
            Shopware()->Container()->getParameter('swag_bundle.plugin_name'),
            Shopware()->Container()->get('swag_bundle.dependencies.provider')
        );
    }
}
