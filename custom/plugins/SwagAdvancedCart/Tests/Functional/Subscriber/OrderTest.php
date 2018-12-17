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

use SwagAdvancedCart\Subscriber\Order;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class OrderTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_onSaveOrder()
    {
        $arguments = [
            'user' => [
                'id' => 1,
            ],
        ];

        $this->getConnection()->exec(
            "UPDATE s_user_attributes SET `swag_advanced_cart_cookie_name_hash` = 'CookieName' WHERE userID = 1"
        );

        $this->createBasket();

        $sql = 'INSERT INTO `s_user_attributes` (`id`, `userID`, `swag_advanced_cart_cookie_name_hash`) VALUES (\'1\', \'1\', \'CookieName\');';
        $this->getConnection()->exec($sql);

        $request = new \Enlight_Controller_Request_RequestHttp();
        Shopware()->Front()->setRequest($request);

        $_COOKIE['BasketToken_CookieName'] = 'customCookieValue';

        $this->getOrder()->onSaveOrder(new ArgsMock($arguments));

        $result = $this->getResultById(
            $this->getConnection()->fetchAll('SELECT * FROM s_order_basket_saved'),
            '169111'
        );

        $this->assertEmpty($result);
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    private function getOrder()
    {
        return new Order(
            $this->getContainer()->get('swag_advanced_cart.user'),
            $this->getContainer()->get('swag_advanced_cart.basket_utils'),
            $this->getContainer()->get('swag_advanced_cart.dependency_provider')
        );
    }

    /**
     * @param array $result
     * @param $id
     *
     * @return array
     */
    private function getResultById(array $result, $id)
    {
        foreach ($result as $value) {
            if ($value['id'] === $id) {
                return $value;
            }
        }

        return [];
    }

    private function createBasket()
    {
        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/createCart.sql'));
    }
}

class ArgsMock extends \Enlight_Hook_HookArgs
{
    /**
     * @var array
     */
    public $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function get($string)
    {
        return $this->data[$string];
    }
}
