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

namespace SwagLiveShopping\Tests\Functional\Controllers;

use Doctrine\DBAL\Connection;
use SwagLiveShopping\Tests\Functional\Mocks\BackendLiveShoppingMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingRequestMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingResponseMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingViewMock;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class LiveShoppingTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_createLiveShoppingAction_success_false()
    {
        $controller = $this->getController('index');
        $controller->createLiveShoppingAction();

        $expectedResult = [
            'success' => false,
            'code' => 8,
            'message' => 'Undefined index: articleId',
        ];

        $view = $controller->View();
        $this->assertArraySubset($expectedResult, $view->viewAssign);
    }

    public function test_createLiveShoppingAction_success_true()
    {
        $controller = $this->getController('index');
        $postData = require __DIR__ . '/_fixtures/createLiveShoppingPostData.php';

        /** @var LiveShoppingRequestMock $request */
        $request = $controller->Request();
        $request->setParams($postData);

        $controller->createLiveShoppingAction();

        $view = $controller->View();

        $expectedResult = [
            'success' => true,
        ];

        $expectedData = [
            'articleId' => 272,
            'type' => 1,
            'name' => 'My Liveshopping',
            'active' => 1,
            'number' => '08154711',
        ];

        $this->assertArraySubset($expectedResult, $view->viewAssign);
        $this->assertArraySubset($expectedData, $view->viewAssign['data']);
        $this->assertTrue(isset($view->viewAssign['data']['id']));
    }

    public function test_updateLiveShoppingAction()
    {
        $controller = $this->getController('index');
        $postData = require __DIR__ . '/_fixtures/createLiveShoppingPostData.php';

        /** @var LiveShoppingRequestMock $request */
        $request = $controller->Request();
        $request->setParams($postData);
        $controller->createLiveShoppingAction();
        /** @var LiveShoppingViewMock $view */
        $view = $controller->View();
        $view->resetAssign();

        $request->params['name'] = 'new LiveShopping name';
        $request->params['number'] = '08154712';

        $controller->updateLiveShoppingAction();

        $id = $view->viewAssign['data']['id'];

        $sql = 'SELECT name, order_number FROM s_articles_lives WHERE id = :id';

        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $result = $connection->fetchAssoc($sql, ['id' => $id]);

        $expectedData = [
            'name' => 'new LiveShopping name',
            'order_number' => '08154712',
        ];

        $this->assertArraySubset($expectedData, $result);
    }

    public function test_deleteLiveShoppingAction()
    {
        $controller = $this->getController('index');
        $postData = require __DIR__ . '/_fixtures/createLiveShoppingPostData.php';

        /** @var LiveShoppingRequestMock $request */
        $request = $controller->Request();
        $request->setParams($postData);
        $controller->createLiveShoppingAction();

        /** @var LiveShoppingViewMock $view */
        $view = $controller->View();
        $id = $view->viewAssign['data']['id'];

        $controller->request->params = [];
        $controller->request->setParam('id', $id);

        $controller->deleteLiveShoppingAction();

        $sql = 'SELECT name, order_number FROM s_articles_lives WHERE id = :id';

        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $result = $connection->fetchAssoc($sql, ['id' => $id]);

        $this->assertFalse($result);
    }

    public function test_getListAction()
    {
        $controller = $this->getController('index');
        $postData = require __DIR__ . '/_fixtures/createLiveShoppingPostData.php';

        /** @var LiveShoppingRequestMock $request */
        $request = $controller->Request();
        $request->setParams($postData);
        $controller->createLiveShoppingAction();
        $controller->request->params = [];
        $controller->request->setParam('articleId', 272);

        $controller->getListAction();

        /** @var LiveShoppingViewMock $view */
        $view = $controller->View();
        $result = $view->viewAssign;

        $expectedData = [
            'articleId' => 272,
            'type' => 1,
            'name' => 'My Liveshopping',
            'active' => 1,
            'number' => '08154711',
        ];

        $this->assertArraySubset(['success' => true], $result);
        $this->assertArraySubset($expectedData, $view->viewAssign['data'][0]);
    }

    public function test_getDetailAction()
    {
        $controller = $this->getController('index');
        $postData = require __DIR__ . '/_fixtures/createLiveShoppingPostData.php';

        /** @var LiveShoppingRequestMock $request */
        $request = $controller->Request();
        $request->setParams($postData);
        $controller->createLiveShoppingAction();
        $controller->request->params = [];

        /** @var LiveShoppingViewMock $view */
        $view = $controller->View();
        $id = $view->viewAssign['data']['id'];
        $controller->request->setParam('id', $id);
        $view->resetAssign();

        $controller->getDetailAction();

        $expectedResult = [
            'success' => true,
        ];

        $expectedData = [
            'articleId' => 272,
            'type' => 1,
            'name' => 'My Liveshopping',
            'active' => 1,
            'number' => '08154711',
        ];

        $result = $view->viewAssign;

        $this->assertArraySubset($expectedResult, $result);
        $this->assertArraySubset($expectedData, $result['data']);
    }

    /**
     * @param string $actionName
     *
     * @return BackendLiveShoppingMock
     */
    private function getController($actionName)
    {
        return new BackendLiveShoppingMock(
            new LiveShoppingRequestMock($actionName),
            new LiveShoppingResponseMock()
        );
    }
}
