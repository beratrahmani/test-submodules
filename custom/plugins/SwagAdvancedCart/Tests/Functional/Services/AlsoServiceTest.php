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

namespace SwagAdvancedCart\Tests\Services;

use SwagAdvancedCart\Services\ProductsAlsoInListService;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class AlsoServiceTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_getBasketIds()
    {
        $service = $this->getAlsoService();

        $this->installSavedItems();

        $reflectionClass = new \ReflectionClass(ProductsAlsoInListService::class);
        $method = $reflectionClass->getMethod('getBasketIds');
        $method->setAccessible(true);

        $result = $method->invokeArgs($service, ['SW10178']);

        $expectedArray = [
            '1688545',
            '1688546',
        ];

        $this->assertArraySubset($expectedArray, $result);
        $this->assertCount(2, $result);
    }

    public function test_getOrderNumbers()
    {
        $service = $this->getAlsoService();

        $this->installSavedItems();

        $reflectionClass = new \ReflectionClass(ProductsAlsoInListService::class);
        $method = $reflectionClass->getMethod('getOrderNumbers');
        $method->setAccessible(true);

        $basketIdArray = [
            '1688545',
            '1688546',
        ];

        $result = $method->invokeArgs($service, [$basketIdArray, 'SW10178']);

        $expectedArray = [
            'SW10014',
            'SW10017',
        ];

        $this->assertArraySubset($expectedArray, $result);
        $this->assertCount(2, $result);
    }

    public function test_getAlsoList()
    {
        $service = $this->getAlsoService();

        $this->installSavedItems();

        $result = $service->getAlsoProductsList('SW10178');

        $this->assertCount(2, $result);
    }

    private function getAlsoService()
    {
        return new ProductsAlsoInListService(
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('shopware_storefront.list_product_service'),
            $this->getContainer()->get('legacy_struct_converter')
        );
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function installSavedItems()
    {
        $sql = 'INSERT INTO `s_order_basket_saved_items` (`id`, `basket_id`, `article_ordernumber`, `quantity`) VALUES
            (2, 1688545, "SW10014", 1),
            (4, 1688546, "SW10014", 1),
            (3, 1688545, "SW10017", 1),
            (5, 1688546, "SW10017", 1),
            (9, 1688547, "SW10117", 1),
            (8, 1688544, "SW10118.1", 1),
            (6, 1688545, "SW10178", 1),
            (7, 1688546, "SW10178", 1);';

        $this->getContainer()->get('dbal_connection')->executeQuery($sql);
    }
}
