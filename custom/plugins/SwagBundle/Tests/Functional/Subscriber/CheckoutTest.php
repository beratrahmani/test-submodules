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

use Enlight_Template_Manager;
use Enlight_View_Default;
use SwagBundle\Subscriber\Checkout;
use SwagBundle\Tests\DatabaseTestCaseTrait;
use SwagBundle\Tests\Functional\BundleControllerTestCase;
use SwagBundle\Tests\Functional\Mocks\FrontendControllerMock;

class CheckoutTest extends BundleControllerTestCase
{
    use DatabaseTestCaseTrait;

    public function test_onCheckoutPostDispatch_should_add_view_assigns_valid_bundle()
    {
        $checkoutSubscriber = $this->getCheckoutSubscriber();
        $this->Request()->setParam('sBundleValidation', 'foo');

        $view = new Enlight_View_Default(
            new Enlight_Template_Manager()
        );

        $controllerMock = new FrontendControllerMock(
            $view,
            $this->Request()
        );

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs(['subject' => $controllerMock]);
        $checkoutSubscriber->onCheckoutPostDispatch($enlightEventArgs);

        self::assertEquals('foo', $this->Request()->getParam('sBundleValidation'));
    }

    public function test_onCheckoutPreDispatch_should_return_null_no_validation()
    {
        $checkoutSubscriber = $this->getCheckoutSubscriber();

        $view = new Enlight_View_Default(
            new Enlight_Template_Manager()
        );

        $controllerMock = new FrontendControllerMock(
            $view,
            $this->Request()
        );

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs(['subject' => $controllerMock]);
        self::assertNull($checkoutSubscriber->onCheckoutPreDispatch($enlightEventArgs));
    }

    public function test_onCheckoutPreDispatch_should_add_validation_view_and_forward_invalid_bundle()
    {
        $checkoutSubscriber = $this->getCheckoutSubscriber();

        $session = 'mySession3';
        $basketId = 13030;

        Shopware()->Container()->get('session')->offsetSet('sessionId', $session);

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket_invalid_customergroup.sql'),
            [':bundleId' => 10300, ':sessionId' => $session, ':basketId' => $basketId]
        );

        $this->Request()->setActionName('finish');

        $view = new Enlight_View_Default(
            new Enlight_Template_Manager()
        );

        $controllerMock = new FrontendControllerMock(
            $view,
            $this->Request()
        );

        $enlightEventArgs = new \Enlight_Controller_ActionEventArgs(['subject' => $controllerMock]);
        $checkoutSubscriber->onCheckoutPreDispatch($enlightEventArgs);

        self::assertArrayHasKey('sBundleValidation', $view->getAssign());
        self::assertArrayHasKey('notForCustomerGroup', $view->getAssign('sBundleValidation')[0]);
        self::assertEquals('confirm', $this->Request()->getActionName());
    }

    /**
     * @return Checkout
     */
    private function getCheckoutSubscriber()
    {
        return new Checkout(
            Shopware()->Container()->get('swag_bundle.bundle_basket'),
            Shopware()->Container()->get('swag_bundle.bundle_component'),
            Shopware()->Container()->get('models'),
            Shopware()->Container()->get('shopware.plugin.cached_config_reader'),
            Shopware()->Container()->getParameter('swag_bundle.plugin_name'),
            Shopware()->Container()->get('swag_bundle.dependencies.provider'),
            Shopware()->Container()->get('config'),
            Shopware()->Container()->get('swag_bundle.voucher_service'),
            Shopware()->Container()->get('dbal_connection')
        );
    }
}
