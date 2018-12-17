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

namespace SwagAdvancedCart\Tests\Functional\Controllers\Frontend;

require_once __DIR__ . '/../../../../Controllers/Frontend/Wishlist.php';

use Enlight_Controller_Request_RequestTestCase;
use Enlight_View_Default;
use Shopware\Components\DependencyInjection\Container;
use SwagAdvancedCart\Services\Dependencies\PluginDependencies;
use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class WishlistTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;

    public function test_removeExpiredCarts_removes_only_carts_without_name()
    {
        $this->createExpiredCarts();
        $this->loginCustomer();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );
        Shopware()->Front()->setRequest($request);

        $controller = new WishlistMock($view, $request, $this->getContainer());
        // On each preDispatch removes AdvancedCart all expired carts
        // Shopware_Controllers_Frontend_Wishlist::preDispatch()
        $controller->preDispatch();

        $result = $this->readCurrentCarts();

        $expectedResult = require __DIR__ . '/Results/removeExpireCartsResult.php';
        $this->assertEquals($expectedResult, $result);
    }

    public function test_saveAction_wishlist_name_should_be_escaped()
    {
        $wishListName = "SAVE<script>alert('test');</script>";

        $this->loginCustomer();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request->setMethod('POST');
        $request->setPost('published', true);
        $request->setPost('name', $wishListName);

        Shopware()->Front()->setRequest($request);

        $this->addArticleToCart();

        $this->getContainer()->set(
            'swag_advanced_cart.plugin_dependency',
            new PluginDependencies($this->getConnection())
        );

        $controller = new WishlistMock($view, $request, $this->getContainer());

        ob_start();
        $controller->saveAction();
        ob_end_clean();

        $result = $this->getWishListName('SAVE');

        $this->assertSame("SAVEalert('test');", $result);
    }

    public function test_saveAction_there_should_be_a_json_response_with_success_false_error_110()
    {
        $this->loginCustomer();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request->setMethod('POST');
        $request->setPost('published', true);
        $request->setPost('name', '');

        Shopware()->Front()->setRequest($request);

        $this->addArticleToCart();

        $this->getContainer()->set(
            'swag_advanced_cart.plugin_dependency',
            new PluginDependencies($this->getConnection())
        );

        $controller = new WishlistMock($view, $request, $this->getContainer());
        $controller->saveAction();

        $this->expectOutputString('{"success":false,"error":[110]}');
    }

    public function test_saveAction_there_should_be_a_json_response_with_success_false_error_120()
    {
        $this->loginCustomer();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request->setMethod('POST');
        $request->setPost('published', true);
        $request->setPost('name', 'Existent Name');

        Shopware()->Front()->setRequest($request);

        $this->addArticleToCart();
        $this->createExistentWishList();

        $this->getContainer()->set(
            'swag_advanced_cart.plugin_dependency',
            new PluginDependencies($this->getConnection())
        );

        $controller = new WishlistMock($view, $request, $this->getContainer());
        $controller->saveAction();

        $this->expectOutputString('{"success":false,"error":[120]}');
    }

    public function test_saveAction_there_should_be_a_json_response_with_success_true()
    {
        $this->loginCustomer();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request->setMethod('POST');
        $request->setPost('published', true);
        $request->setPost('name', 'nonExistent Name');

        Shopware()->Front()->setRequest($request);

        $this->addArticleToCart();
        $this->createExistentWishList();

        $this->getContainer()->set(
            'swag_advanced_cart.plugin_dependency',
            new PluginDependencies($this->getConnection())
        );

        $controller = new WishlistMock($view, $request, $this->getContainer());

        ob_start();
        $controller->saveAction();
        $output = json_decode($this->getActualOutput(), true);
        ob_end_clean();

        $subset = [
            'success' => true,
            'customizedItem' => false,
            'regularItem' => true,
            'requireBundleMessage' => false,
        ];

        $this->assertArraySubset($subset, $output);
    }

    public function test_createCartAction_wishlist_name_should_be_escaped()
    {
        $wishListName = 'CREATE<script>alert(":P");</script>';

        $this->loginCustomer();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request->setMethod('POST');
        $request->setPost('published', true);
        $request->setPost('name', $wishListName);

        Shopware()->Front()->setRequest($request);

        $controller = new WishlistMock($view, $request, $this->getContainer());

        ob_start();
        $controller->createCartAction();
        ob_end_clean();

        $result = $this->getWishListName('CREATE');

        $this->assertSame('CREATEalert(":P");', $result);
    }

    public function test_changeNameAction_wishlist_name_should_be_escaped()
    {
        $this->loginCustomer();

        $oldBasketName = 'OldName';

        // Create a wishList
        $cartHandler = $this->getContainer()->get('swag_advanced_cart.cart_handler');
        $cartHandler->createWishList($oldBasketName);

        $wishListName = 'CHANGE<script>alert(":P");</script>';

        $basketId = $this->getBasketId($oldBasketName);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request->setMethod('POST');
        $request->setPost('published', true);
        $request->setPost('name', $oldBasketName);
        $request->setPost('basketId', $basketId);
        $request->setPost('newName', $wishListName);

        Shopware()->Front()->setRequest($request);

        $controller = new WishlistMock($view, $request, $this->getContainer());

        $controller->preDispatch();
        $controller->changeNameAction();

        $result = $this->getWishListName('CHANGE');

        $this->assertSame('CHANGEalert(":P");', $result);
    }

    public function test_getArticleAction_add_new_product()
    {
        $this->loginCustomer();
        $this->createExistentWishList();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(
            $this->getContainer()->get('template')
        );

        $request->setMethod('POST');
        $request->setPost('basketId', 169111);
        $request->setPost('articleName', 'SW10009');

        Shopware()->Front()->setRequest($request);

        $controller = new WishlistMock($view, $request, $this->getContainer());
        $pluginPath = $this->getContainer()->getParameter('swag_advanced_cart.plugin_dir');
        $controller->View()->addTemplateDir($pluginPath . '/Resources/views/');

        ob_start();
        $controller->preDispatch();
        // The @ is for suppressing PHP notices, which occurs in smarty code
        @$controller->getArticleAction();
        $result = ob_get_contents();
        ob_end_clean();

        $result = json_decode($result, true);
        $this->assertTrue($result['success']);
        $this->assertEquals('added', $result['type']);
        $this->assertEquals('SW10009', $result['data']['ordernumber']);
    }

    public function test_getArticleAction_invalid_customer()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $request->setMethod('POST');
        $request->setPost('basketId', 169111);
        $request->setPost('articleName', 'SW10239');

        Shopware()->Front()->setRequest($request);

        $controller = new WishlistMock($view, $request, $this->getContainer());
        $pluginPath = $this->getContainer()->getParameter('swag_advanced_cart.plugin_dir');
        $controller->View()->addTemplateDir($pluginPath . '/Resources/views/');

        $controller->preDispatch();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User not authorized');
        $controller->getArticleAction();
    }

    /**
     * @return Container
     */
    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    private function getBasketId($name)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();

        return $queryBuilder->select('id')
            ->from('s_order_basket_saved')
            ->where('name LIKE :name')
            ->setParameter('name', $name)
            ->execute()
            ->fetchColumn();
    }

    private function addArticleToCart()
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->update('s_order_basket')
            ->set('sessionID', '"sessionId"')
            ->set('userID', 1)
            ->where("sessionID LIKE 'b2e40c5e67a143e57c91bd57ba0851d6303b51c3'")
            ->execute();
    }

    private function getWishListName($name)
    {
        $name = '%' . $name . '%';
        $queryBuilder = $this->getConnection()->createQueryBuilder();

        return $queryBuilder->select('name')
            ->from('s_order_basket_saved')
            ->where('name LIKE :name')
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->execute()
            ->fetchColumn();
    }

    private function createExpiredCarts()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/carts.sql');
        $this->getConnection()->exec($sql);
    }

    private function readCurrentCarts()
    {
        $qb = $this->getConnection()->createQueryBuilder();

        return $qb->select(['cookie_value', 'user_id', 'shop_id', 'expire', 'modified', 'name', 'published'])
            ->from('s_order_basket_saved')
            ->execute()
            ->fetchAll();
    }

    private function createExistentWishList()
    {
        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/wishList.sql'));
    }
}

class WishlistMock extends \Shopware_Controllers_Frontend_Wishlist
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

    public function get($string)
    {
        return $this->container->get($string);
    }

    public function redirect($url, array $options = [])
    {
        $this->indexAction();
    }
}
