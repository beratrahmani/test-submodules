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

use Doctrine\DBAL\Connection;
use Shopware\Components\DependencyInjection\Container;
use SwagAdvancedCart\Subscriber\Basket;
use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class BasketTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;

    public function test_beforeDeleteArticle()
    {
        $this->createBasket();

        $request = new \Enlight_Controller_Request_RequestTestCase();
        Shopware()->Front()->setRequest($request);

        $arguments = [
            'id' => 1177111,
            'user' => [
                'id' => 1,
            ],
        ];

        $this->getBasket()->beforeDeleteArticle(new HookArgsMock($arguments));

        $this->assertEmpty($this->getArticle(1177111));
    }

    public function test_afterAddArticle()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        Shopware()->Front()->setRequest($request);

        $_COOKIE['BasketToken_CookieName'] = 'customCookieValue';

        $sql = 'INSERT INTO `s_user_attributes` (`id`, `userID`, `swag_advanced_cart_cookie_name_hash`) VALUES (\'1\', \'1\', \'CookieName\');';
        $this->getConnection()->exec($sql);

        $arguments = [
            'id' => 'SW10083',
            'quantity' => 5,
            'user' => [
                'id' => 1,
            ],
        ];

        $this->getBasket()->afterAddArticle(new HookArgsMock($arguments));
        $subset = [
            'article_ordernumber' => 'SW10083',
            'quantity' => '5',
        ];
        $result = $this->getSavedItemByOrderNumber(
            $this->getConnection()->fetchAll('SELECT * FROM s_order_basket_saved_items'),
            'SW10083'
        );

        $this->assertArraySubset($subset, $result);
    }

    /**
     * @return Container
     */
    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    /**
     * @return Connection
     */
    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    /**
     * @return Basket
     */
    private function getBasket()
    {
        return new Basket(
            $this->getContainer()->get('swag_advanced_cart.basket_utils'),
            $this->getContainer()->get('swag_advanced_cart.user'),
            $this->getContainer()->get('swag_advanced_cart.dependency_provider'),
            $this->getContainer()->get('events')
        );
    }

    /**
     * @param array  $result
     * @param string $orderNumber
     *
     * @return array
     */
    private function getSavedItemByOrderNumber(array $result, $orderNumber)
    {
        foreach ($result as $value) {
            if ($value['article_ordernumber'] === $orderNumber) {
                return $value;
            }
        }

        return [];
    }

    private function createBasket()
    {
        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/createCart.sql'));
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getArticle($id)
    {
        return $this->getConnection()->fetchAll('SELECT * FROM s_order_basket_saved_items WHERE id = :id', ['id' => $id]);
    }
}

class HookArgsMock extends \Enlight_Hook_HookArgs
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
