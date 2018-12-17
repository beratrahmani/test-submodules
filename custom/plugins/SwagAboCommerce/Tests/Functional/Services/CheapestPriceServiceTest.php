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

namespace SwagAboCommerce\Tests\Functional\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use SwagAboCommerce\Services\CheapestPriceService;
use SwagAboCommerce\Tests\Functional\KernelTestCaseTrait;

class CheapestPriceServiceTest extends TestCase
{
    use KernelTestCaseTrait;

    /**
     * @dataProvider test_getNewCheapestPrice_dataProvider
     */
    public function test_getNewCheapestPrice($listProductStruct, $percntageDiscount, $abspluteDiscount, $expected)
    {
        $service = $this->getReferencePriceService();

        $reflectionClass = new \ReflectionClass(CheapestPriceService::class);
        $method = $reflectionClass->getMethod('getNewCheapestPrice');
        $method->setAccessible(true);

        $result = $method->invokeArgs($service, [$listProductStruct, $percntageDiscount, $abspluteDiscount]);

        $this->assertSame($expected, $result);
    }

    public function test_getNewCheapestPrice_dataProvider()
    {
        $struct = require __DIR__ . '/_fixtures/ListProductStruct.php';

        return [
            [$struct, null, null, 19.99],
            [$struct, 0.0, 0.0, 19.99],
            [$struct, 1.00, 0.0, 19.7901],
            [$struct, 5.00, 0.0, 18.9905],
            [$struct, 10.00, 0.0, 17.991],
            [$struct, 0.00, 1.00, 18.8],
            [$struct, 0.00, 5.00, 14.04],
            [$struct, 0.00, 10.00, 8.09],
            [$struct, 10.00, 1.00, 17.991],
            [$struct, 10.00, 2.00, 17.61],
            [$struct, 20.00, 2.00, 15.991999999999999],
            [$struct, 20.00, 3.00, 15.991999999999999],
            [$struct, 20.00, 4.00, 15.229999999999999],
            [$struct, 10.00, 10.00, 8.09],
        ];
    }

    public function test_GetAboPrices()
    {
        $this->createAboPrices();
        $service = $this->getReferencePriceService();
        $reflectionClass = new \ReflectionClass(CheapestPriceService::class);
        $method = $reflectionClass->getMethod('getAboPrices');
        $method->setAccessible(true);

        $result = $method->invoke($service);

        $expectedResult = [
            [
                'discount_percent' => '10',
            ],
            [
                'discount_absolute' => '12.605042016807',
            ],
            [
                'discount_percent' => '22.5',
            ],
            [
                'discount_absolute' => '13.865546218487',
            ],
        ];

        $this->assertArraySubset($expectedResult, $result);
    }

    /**
     * @dataProvider test_GetMaxPercentageDiscount_dataProvider
     *
     * @param array $data
     * @param $expectedResult
     *
     * @throws \ReflectionException
     */
    public function test_GetMaxPercentageDiscount(array $data, $expectedResult)
    {
        $service = $this->getReferencePriceService();
        $reflectionClass = new \ReflectionClass(CheapestPriceService::class);
        $method = $reflectionClass->getMethod('getMaxPercentageDiscount');
        $method->setAccessible(true);

        $result = $method->invoke($service, $data);

        $this->assertSame($expectedResult, $result);
    }

    public function test_GetMaxPercentageDiscount_dataProvider()
    {
        $data = require __DIR__ . '/_fixtures/abo_prices_test_data.php';

        return [
            [$data['testCase1'], 22.5],
            [$data['testCase2'], 11.375],
            [$data['testCase3'], 0.0],
            [$data['testCase4'], 88.65],
            [$data['testCase5'], 15.6],
        ];
    }

    /**
     * @dataProvider test_GetMaxAbsoluteDiscount_dataProvider
     */
    public function test_GetMaxAbsoluteDiscount(array $data, $expectedResult)
    {
        $service = $this->getReferencePriceService();
        $reflectionClass = new \ReflectionClass(CheapestPriceService::class);
        $method = $reflectionClass->getMethod('getMaxAbsoluteDiscount');
        $method->setAccessible(true);

        $result = $method->invoke($service, $data);

        $this->assertSame($expectedResult, $result);
    }

    public function test_GetMaxAbsoluteDiscount_dataProvider()
    {
        $data = require __DIR__ . '/_fixtures/abo_prices_test_data.php';

        return [
            [$data['testCase1'], 13.865546218487],
            [$data['testCase2'], 0.0],
            [$data['testCase3'], 5.0],
            [$data['testCase4'], 31.75],
            [$data['testCase5'], 6.55487],
        ];
    }

    public function test_updateCheapestPrice()
    {
        $this->createAboPrices();
        $service = $this->getReferencePriceService();
        $product = require __DIR__ . '/_fixtures/ListProductStruct.php';
        $expectedResult = $product;
        $expectedAttribute = new Attribute([
            'cheapest_abo_price' => 3.490000000000471,
        ]);
        $expectedResult->addAttribute('swag_abo_commerce_prices', $expectedAttribute);

        $result = $service->updateCheapestPrice($product);

        $this->assertEquals($expectedResult, $result);
    }

    private function createAboPrices()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/swag_abo_commerce_abo_prices_data.sql');
        $dbal = $this->getDbalConnection();
        $dbal->executeQuery($sql);
    }

    private function getReferencePriceService()
    {
        return new CheapestPriceService($this->getContainer()->get('dbal_connection'));
    }
}
