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

namespace SwagLiveShopping\Tests\Functional\Controllers\Frontend;

use Doctrine\DBAL\Connection;
use SwagLiveShopping\Tests\Functional\Mocks\FrontendLiveShoppingMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingRequestMock;
use SwagLiveShopping\Tests\Functional\Mocks\LiveShoppingResponseMock;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class LiveShoppingTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_index_action()
    {
        $this->installLiveShoppingProduct();

        $controller = $this->getController('index');

        $controller->indexAction();
        $view = $controller->View();

        $expectedData = [
            'SW10178' => [
                'articleID' => 178,
                'ordernumber' => 'SW10178',
            ],
        ];

        $assignedData = $view->viewAssign;

        self::assertSame(1, $assignedData['sNumberArticles']);
        self::assertArraySubset($expectedData, $assignedData['sArticles']);
        self::assertArrayHasKey('live_shopping', $assignedData['sArticles']['SW10178']['attributes']);
    }

    private function installLiveShoppingProduct()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = $this->getContainer()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/../../Components/_fixtures/LiveShoppingProduct.sql');
        $databaseConnection->exec($sql);
    }

    /**
     * @param string $actionName
     *
     * @return FrontendLiveShoppingMock
     */
    private function getController($actionName)
    {
        return new FrontendLiveShoppingMock(
            new LiveShoppingRequestMock($actionName),
            new LiveShoppingResponseMock()
        );
    }
}
