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

namespace SwagAdvancedCart\Tests\Functional\Controllers\Backend;

require_once __DIR__ . '/../../../../Controllers/Backend/SwagAdvancedCart.php';

use Enlight_Controller_Request_RequestTestCase;
use Enlight_View_Default;
use Shopware\Components\DependencyInjection\Container;
use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class SwagAdvancedCartTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;

    public function test_getList_there_should_be_2_items()
    {
        $this->createView();
        $this->createWishLists();

        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams(['limit' => 2, 'offset' => 0]);

        $controller = new ControllerMock($view, $request, $this->getContainer());
        $controller->listAction();

        $this->assertCount(2, $controller->View()->getAssign('data'));
    }

    public function test_getList_there_should_be_3_items()
    {
        $this->createView();
        $this->createWishLists();

        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams(['offset' => 0]);

        $controller = new ControllerMock($view, $request, $this->getContainer());

        $controller->listAction();

        $this->assertCount(3, $controller->View()->getAssign('data'));
    }

    public function test_articles_there_should_be_4_articles()
    {
        $this->createView();
        $this->createWishLists();
        $id = $this->getConnection()->fetchColumn(
            'SELECT id FROM s_order_basket_saved WHERE cookie_value = :cookieValue;',
            ['cookieValue' => 'customCookieValue']
        );

        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams(['id' => $id]);

        $controller = new ControllerMock($view, $request, $this->getContainer());

        $controller->articlesAction();

        $data = $controller->View()->getAssign('data');

        $this->assertCount(4, $data);
        $this->assertArraySubset(require(__DIR__ . '/Results/articleResult.php'), $data);
    }

    public function test_deleteItems_the_result_should_be_empty()
    {
        $this->createView();
        $this->installBasket();

        $id = $this->getItemId('testCookieValue');

        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams(['id' => $id]);

        $controller = new ControllerMock($view, $request, $this->getContainer());

        $controller->deleteItemsAction();

        $this->assertEmpty($this->getBasketById($id));
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    private function createView()
    {
        $this->_view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );
    }

    private function createWishLists()
    {
        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/createWishlists.sql'));
    }

    /**
     * @param string $cookieValue
     *
     * @return int | mixed
     */
    private function getItemId($cookieValue)
    {
        $cartId = $this->getConnection()->fetchColumn(
            'SELECT id FROM s_order_basket_saved WHERE cookie_value = :cookieValue;',
            ['cookieValue' => $cookieValue]
        );

        return $this->getConnection()->fetchColumn(
            'SELECT id FROM s_order_basket_saved_items WHERE basket_id = :basketId',
            ['basketId' => $cartId]
        );
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getBasketById($id)
    {
        return $this->getConnection()->fetchAll('SELECT * FROM s_order_basket_saved WHERE id = :id', ['id' => $id]);
    }

    private function installBasket()
    {
        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/simpleBasket.sql'));
    }
}

class ControllerMock extends \Shopware_Controllers_Backend_SwagAdvancedCart
{
    /**
     * @var Enlight_Controller_Request_RequestTestCase
     */
    public $request;
    /**
     * @var Enlight_View_Default
     */
    public $view;

    /**
     * @var Container
     */
    public $container;

    /**
     * @param Enlight_View_Default                       $view
     * @param Enlight_Controller_Request_RequestTestCase $request
     * @param Container                                  $container
     */
    public function __construct(Enlight_View_Default $view, Enlight_Controller_Request_RequestTestCase $request, Container $container)
    {
        $this->view = $view;
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function Request()
    {
        return $this->request;
    }

    /**
     * @return Enlight_View_Default
     */
    public function View()
    {
        return $this->view;
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function get($string)
    {
        return $this->container->get($string);
    }
}
