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

namespace SwagBundle\Tests\Functional\Controllers\Widgets;

require_once __DIR__ . '/../../../../Controllers/Widgets/Bundle.php';

use SwagBundle\Tests\DatabaseTestCaseTrait;
use SwagBundle\Tests\Functional\BundleControllerTestCase;
use SwagBundle\Tests\Functional\Mocks\BundleWidgetsControllerMock;

class BundleTest extends BundleControllerTestCase
{
    use DatabaseTestCaseTrait;

    public function test_addBundleToBasketAction_should_return_no_bundle_id()
    {
        $view = new \Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $this->Request()->setParam('bundleId', 0);

        $bundleCtl = new BundleWidgetsControllerMock(
            $this->Request(),
            $this->Response(),
            Shopware()->Container(),
            $view
        );

        $return = $bundleCtl->addBundleToBasketAction();

        self::assertNull($return);
    }

    public function test_addBundleToBasketAction_should_redirect_to_checkout_cart()
    {
        $view = new \Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $bundleId = 16020;

        $this->Request()->setParam('bundleId', $bundleId);

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/../_fixtures/default_bundle.sql'),
            [':bundleId' => $bundleId]
        );

        $bundleCtl = new BundleWidgetsControllerMock(
            $this->Request(),
            $this->Response(),
            Shopware()->Container(),
            $view
        );

        $bundleCtl->addBundleToBasketAction();
        $headers = $this->Response()->getHeaders();
        $strPosRedirect = strpos($headers[0]['value'], 'checkout/cart');

        self::assertNotFalse($strPosRedirect);
        self::assertTrue($strPosRedirect > 0);
    }

    public function test_addBundleToBasketAction_should_redirect_to_product_error()
    {
        $view = new \Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $bundleId = 16020;

        $this->Request()->setParam('bundleId', $bundleId);

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/../_fixtures/bundle_without_prices.sql'),
            [':bundleId' => $bundleId]
        );

        $bundleCtl = new BundleWidgetsControllerMock(
            $this->Request(),
            $this->Response(),
            Shopware()->Container(),
            $view
        );

        $bundleCtl->addBundleToBasketAction();
        $headers = $this->Response()->getHeaders();
        $strPosRedirect = strpos($headers[0]['value'], 'detail/index');

        self::assertNotFalse($strPosRedirect);
        self::assertTrue($strPosRedirect > 0);
    }

    public function test_addBundleToBasketAction_should_redirect_to_product_error_because_bundle_does_not_exist()
    {
        $view = new \Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $this->Request()->setParam('bundleId', 9999);

        $bundleCtl = new BundleWidgetsControllerMock(
            $this->Request(),
            $this->Response(),
            Shopware()->Container(),
            $view
        );

        $bundleCtl->addBundleToBasketAction();
        $headers = $this->Response()->getHeaders();
        $strPosRedirect = strpos($headers[0]['value'], 'detail/index');

        self::assertNotFalse($strPosRedirect);
        self::assertTrue($strPosRedirect > 0);
    }

    public function test_addBundleToBasketAction_should_redirect_to_product_error_because_bundle_is_inactive()
    {
        $view = new \Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $bundleId = 16021;

        $this->Request()->setParam('bundleId', $bundleId);

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/../_fixtures/bundle_inactive.sql'),
            [':bundleId' => $bundleId]
        );

        $bundleCtl = new BundleWidgetsControllerMock(
            $this->Request(),
            $this->Response(),
            Shopware()->Container(),
            $view
        );

        $bundleCtl->addBundleToBasketAction();
        $headers = $this->Response()->getHeaders();
        $strPosRedirect = strpos($headers[0]['value'], 'detail/index');

        self::assertNotFalse($strPosRedirect);
        self::assertTrue($strPosRedirect > 0);
    }
}
