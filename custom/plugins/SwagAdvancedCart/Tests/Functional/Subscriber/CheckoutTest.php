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

namespace SwagAdvancedCart\Tests\Functional\Subscriber;

use SwagAdvancedCart\Subscriber\Checkout;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class CheckoutTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_onCheckout_there_should_be_data_in_the_view()
    {
        $this->getContainer()->get('session')->offsetSet('sUserId', 1);
        $this->createWishList();

        $view = new \Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('cart');
        $subject = new SubjectMock($view, $request);
        $arguments = new ArgumentsMock([], $subject, $request);

        Shopware()->Front()->setRequest($request);

        $this->getCheckout()->onCheckout($arguments);

        $result = $view->getAssign('wishlists');
        $result = array_shift($result);

        $subset = [
            'cookie_value' => 'customCookieValue',
            'user_id' => '1',
            'shop_id' => '1',
            'name' => 'Wishlist name',
        ];

        $this->assertArraySubset($subset, $result);
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    private function getCheckout()
    {
        return new Checkout(
            $this->getContainer()->get('swag_advanced_cart.basket_utils'),
            $this->getContainer()->get('swag_advanced_cart.user'),
            $this->getContainer()->get('swag_advanced_cart.dependency_provider')
        );
    }

    private function createWishList()
    {
        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/createWishlist.sql'));
    }
}

class SubjectMock
{
    /**
     * @var \Enlight_View_Default
     */
    public $view;

    /**
     * @var RequestMock
     */
    public $requestMock;

    /**
     * @param \Enlight_View_Default $view
     * @param $requestMock
     */
    public function __construct(\Enlight_View_Default $view, $requestMock)
    {
        $this->view = $view;
        $this->requestMock = $requestMock;
    }

    /**
     * @return \Enlight_View_Default
     */
    public function View()
    {
        return $this->view;
    }

    public function Request()
    {
        return $this->requestMock;
    }
}

class ArgumentsMock extends \Enlight_Hook_HookArgs
{
    /**
     * @var array
     */
    public $data;

    /**
     * @var SubjectMock
     */
    public $subject;

    /**
     * @var \Enlight_Controller_Request_RequestTestCase
     */
    public $request;

    public function __construct(array $data, $subject, $request)
    {
        $this->data = $data;
        $this->subject = $subject;
        $this->request = $request;
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function get($string)
    {
        if ($string === 'subject') {
            return $this->subject;
        }

        return $this->data[$string];
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
