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

namespace SwagPromotion\Tests\Functional;

require_once __DIR__ . '/../../../../Controllers/Api/Promotion.php';

use Shopware\Components\DependencyInjection\Container;
use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\Functional\Controller\Api\Fixtures\PostData;
use SwagPromotion\Tests\PromotionTestCase;

class PromotionTest extends PromotionTestCase
{
    use DatabaseTestCaseTrait;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        parent::setUp();

        $this->container = Shopware()->Container();
    }

    public function test_indexAction()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/Promotions.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $controller = new PromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));
        $controller->init();

        $controller->indexAction();

        $view = $controller->View();
        $result = $view->getAssign();

        $this->assertCount(6, $result['data']);
        $this->assertSame(6, $result['total']);
        $this->assertTrue($result['success']);
    }

    public function test_getAction()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/Promotions.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $this->Request()->setParam('id', 5555666);

        $controller = new PromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));
        $controller->init();

        $controller->getAction();

        $view = $controller->View();
        $result = $view->getAssign();

        $expectedSubset = [
            'id' => 5555666,
            'name' => 'Meine neue Promotion',
            'rules' => '{"and":{"true0.5629873857242442":[null,null,""]}}',
            'applyRules' => '{"and":{"productCompareRule0.33400308810343593":["detail::ordernumber","=","SW10002.1"]}}',
            'type' => 'product.percentage',
            'number' => '08154714',
        ];

        $this->assertTrue($result['success']);
        $this->assertArraySubset($expectedSubset, $result['data']);
    }

    public function test_postAction()
    {
        $postData = new PostData();
        $this->Request()->setPost($postData->getPostData());

        $controller = new PromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));
        $controller->init();

        $controller->postAction();

        $view = $controller->View();
        $result = $view->getAssign();

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data']);
    }

    public function test_putAction()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/Promotions.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $this->Request()->setParam('id', 5555666);
        $this->Request()->setPost([
            'name' => 'TEST TEST TEST',
        ]);

        $controller = new PromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));
        $controller->init();

        $controller->putAction();

        $view = $controller->View();
        $result = $view->getAssign();

        $expectedResult = [
            'id' => 5555666,
            'location' => 'promotion/5555666',
        ];

        $sql = 'SELECT name FROM s_plugin_promotion WHERE id = 5555666';
        $newName = $this->container->get('dbal_connection')->fetchColumn($sql);
        $expectedNewName = 'TEST TEST TEST';

        $this->assertTrue($result['success']);
        $this->assertEquals($expectedResult, $result['data']);
        $this->assertSame($expectedNewName, $newName);
    }

    public function test_deleteAction()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/Promotions.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $this->Request()->setParam('id', 5555666);

        $controller = new PromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));
        $controller->init();

        $controller->deleteAction();

        $view = $controller->View();
        $result = $view->getAssign();

        $sql = 'SELECT name FROM s_plugin_promotion WHERE id = 5555666';
        $nameResult = $this->container->get('dbal_connection')->fetchColumn($sql);

        $this->assertTrue($result['success']);
        $this->assertEmpty($nameResult);
    }
}

class PromotionTestControllerMock extends \Shopware_Controllers_Api_Promotion
{
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
