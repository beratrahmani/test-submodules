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

namespace SwagLiveShopping\Tests\Functional\Controllers\Widgets;

use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingRequestMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingResponseMock;
use SwagLiveShopping\Tests\Functional\Mocks\WidgetsLiveShoppingMock;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class LiveShoppingTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_getLiveShoppingDataAction_should_be_false()
    {
        $controller = $this->getController('index');
        $controller->getLiveShoppingDataAction();
        $success = $controller->View()->getAssign('success');

        $this->assertFalse($success);
    }

    public function test_getLiveShoppingDataAction_should_be_true()
    {
        $controller = $this->getController('index');
        $this->installLiveShoppingVariant();
        $controller->request->setParam('liveShoppingId', 20001);

        Shopware()->Front()->setRequest($controller->request);

        $controller->getLiveShoppingDataAction();
        $result = $controller->View()->getAssign('data');
        $success = $controller->View()->getAssign('success');

        $expectedData = [
            'id' => 20001,
            'name' => 'My variant Liveshopping',
            'type' => 1,
            'number' => '08154712',
        ];

        $this->assertTrue($success);
        $this->assertArraySubset($expectedData, $result);
    }

    private function installLiveShoppingVariant()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShopingVariantProduct.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
    }

    /**
     * @param string $actionName
     *
     * @return WidgetsLiveShoppingMock
     */
    private function getController($actionName)
    {
        return new WidgetsLiveShoppingMock(
            new LiveShoppingRequestMock($actionName),
            new LiveShoppingResponseMock()
        );
    }
}
