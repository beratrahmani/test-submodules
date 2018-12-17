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
use Shopware\Components\DependencyInjection\Container;

/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPromotion\Tests\Functional;

require_once __DIR__ . '/../../../../Controllers/Backend/SwagPromotion.php';

use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\Functional\Controller\Backend\Fixtures\SaveData;
use SwagPromotion\Tests\PromotionTestCase;

class SwagPromotionTest extends PromotionTestCase
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

    public function test_save()
    {
        $data = new SaveData();
        $controller = new SwagPromotionTestControllerMock($this->Request(), $this->Response());
        $controller->save($data->getPromotionData());

        $sql = 'SELECT * FROM s_plugin_promotion';
        $result = array_shift($this->container->get('dbal_connection')->fetchAll($sql));

        $expectedSubset = [
            'name' => 'Meine neue Promotion',
            'rules' => '{"and":{"basketCompareRule0.6230162851519923":["amountGross",">=","60"]}}',
            'apply_rules' => '{"and":{"true1":[]}}',
            'type' => 'basket.absolute',
            'number' => '08154711',
            'max_usage' => '1',
        ];

        $this->assertArraySubset($expectedSubset, $result);
    }

    public function test_getDetail()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/Promotions.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $controller = new SwagPromotionTestControllerMock($this->Request(), $this->Response());
        $result = $controller->getDetail(2555666);

        $expectedSubset = [
            'id' => 2555666,
            'name' => 'Meine neue Promotion',
            'rules' => '{"and":{"basketCompareRule0.6230162851519923":["amountGross",">=","60"]}}',
            'applyRules' => '{"and":{"true1":[]}}',
            'type' => 'basket.absolute',
            'number' => '08154711',
        ];

        $this->assertTrue($result['success']);
        $this->assertArraySubset($expectedSubset, $result['data']);
    }

    public function test_saveRowEditingDataAction()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/Promotions.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $data = new SaveData();

        $this->Request()->setParam(
            'transportData',
            json_encode($data->getPromotionRowEditingUpdateData())
        );

        $controller = new SwagPromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));
        $controller->saveRowEditingDataAction();

        $sql = 'SELECT amount FROM s_plugin_promotion WHERE id = 2555666';
        $amount = $this->container->get('dbal_connection')->fetchColumn($sql);
        $result = $controller->View()->getAssign();

        $expectedAmount = '555';

        $this->assertTrue($result['success']);
        $this->assertSame($expectedAmount, $amount);
    }

    public function test_duplicateRowAction_there_should_be_success_false()
    {
        $this->Request()->setParam('transportData', json_encode(['id' => '']));

        $controller = new SwagPromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));
        $controller->duplicateRowAction();

        $result = $controller->View()->getAssign();

        $this->assertFalse($result['success']);
        $this->assertSame('No valid promotion ID passed', $result['message']);
    }

    public function test_duplicateRowAction_there_should_be_success_true()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/Promotions.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $this->Request()->setParam('transportData', json_encode(['id' => '7555666']));

        $controller = new SwagPromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));
        $controller->duplicateRowAction();

        $result = $controller->View()->getAssign();

        $sql = 'SELECT COUNT(id) FROM s_plugin_promotion WHERE number = 08154716';
        $count = $this->container->get('dbal_connection')->fetchColumn($sql);

        $this->assertSame('2', $count);
        $this->assertTrue($result['success']);
    }

    public function test_deleteRowAction_there_should_be_success_false()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/Promotions.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $this->Request()->setParam('transportData', json_encode(['id' => '']));
        $controller = new SwagPromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));

        $controller->deleteRowAction();

        $result = $controller->View()->getAssign();

        $this->assertFalse($result['success']);
        $this->assertSame('No valid promotion ID passed', $result['message']);
    }

    public function test_deleteRowAction_there_should_be_success_true()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/Promotions.sql');
        $this->container->get('dbal_connection')->exec($sql);

        $this->Request()->setParam('transportData', json_encode(['id' => '7555666']));

        $controller = new SwagPromotionTestControllerMock($this->Request(), $this->Response());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));
        $controller->deleteRowAction();

        $result = $controller->View()->getAssign();

        $sql = 'SELECT COUNT(id) FROM s_plugin_promotion WHERE number = 08154716';
        $count = $this->container->get('dbal_connection')->fetchColumn($sql);

        $this->assertSame('0', $count);
        $this->assertTrue($result['success']);
    }
}

class SwagPromotionTestControllerMock extends \Shopware_Controllers_Backend_SwagPromotion
{
    public function __construct($request, $response)
    {
        $this->container = Shopware()->Container();
        $this->request = $request;
        $this->response = $response;
    }
}
