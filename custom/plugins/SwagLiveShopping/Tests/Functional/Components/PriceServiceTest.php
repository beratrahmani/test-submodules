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

namespace SwagLiveShopping\Tests\Functional\Components;

use SwagLiveShopping\Components\LiveShoppingInterface;
use SwagLiveShopping\Components\LiveShoppingTypeNotSupportedException;
use SwagLiveShopping\Components\NoAssociatedTaxRate;
use SwagLiveShopping\Components\PriceService;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class PriceServiceTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    const EXPECT_NOT_SUPPORTED_EXCEPTION = -999999;

    const EXPECT_NOT_ASSOCIATED_EXCEPTION = -888888;

    /**
     * @dataProvider test_getLiveShoppingPrice_dataProvider
     *
     * @param int       $liveShoppingId
     * @param int       $liveShoppingType
     * @param \DateTime $buyTime
     * @param \DateTime $validFrom
     * @param \DateTime $validTo
     * @param           $expectedResult
     *
     * @throws \SwagLiveShopping\Components\NoLiveShoppingPriceException
     */
    public function test_getLiveShoppingPrice(
        $liveShoppingId,
        $liveShoppingType,
        \DateTime $buyTime,
        \DateTime $validFrom,
        \DateTime $validTo,
        $expectedResult
    ) {
        $this->installLiveShopping();
        $service = $this->getService();

        if ($expectedResult === self::EXPECT_NOT_SUPPORTED_EXCEPTION) {
            $this->expectException(LiveShoppingTypeNotSupportedException::class);
        }

        if ($expectedResult === self::EXPECT_NOT_ASSOCIATED_EXCEPTION) {
            $this->expectException(NoAssociatedTaxRate::class);
        }

        $price = $service->getLiveShoppingPrice(
            $liveShoppingId,
            $liveShoppingType,
            $buyTime,
            $validFrom,
            $validTo
        );

        $this->assertSame($expectedResult, round($price, 2, PHP_ROUND_HALF_UP));
    }

    /**
     * @return array
     */
    public function test_getLiveShoppingPrice_dataProvider()
    {
        return [
            [
                1,
                null,
                new \DateTime('2018-07-20 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                self::EXPECT_NOT_SUPPORTED_EXCEPTION,
            ],
            [
                1,
                4,
                new \DateTime('2018-07-20 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                self::EXPECT_NOT_SUPPORTED_EXCEPTION,
            ],
            [
                1,
                'aaa',
                new \DateTime('2018-07-20 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                self::EXPECT_NOT_SUPPORTED_EXCEPTION,
            ],
            [
                1,
                '1',
                new \DateTime('2018-07-20 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                self::EXPECT_NOT_SUPPORTED_EXCEPTION,
            ],
            [
                1,
                '1',
                new \DateTime('2018-07-20 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                self::EXPECT_NOT_SUPPORTED_EXCEPTION,
            ],
            [
                2,
                LiveShoppingInterface::NORMAL_TYPE,
                new \DateTime('2018-07-20 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                self::EXPECT_NOT_ASSOCIATED_EXCEPTION,
            ],
            [
                1,
                LiveShoppingInterface::NORMAL_TYPE,
                new \DateTime('2018-07-20 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                100.00,
            ],
            [
                1,
                LiveShoppingInterface::NORMAL_TYPE,
                new \DateTime('2018-07-30 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                100.00,
            ],
            [
                1,
                LiveShoppingInterface::DISCOUNT_TYPE,
                new \DateTime('2018-07-20 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                120.92,
            ],
            [
                1,
                LiveShoppingInterface::DISCOUNT_TYPE,
                new \DateTime('2018-07-30 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                100.92,
            ],
            [
                1,
                LiveShoppingInterface::SURCHARGE_TYPE,
                new \DateTime('2018-07-20 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                123.08,
            ],
            [
                1,
                LiveShoppingInterface::SURCHARGE_TYPE,
                new \DateTime('2018-07-30 13:00:00'),
                new \DateTime('2018-07-20 00:00:00'),
                new \DateTime('2018-07-31 00:00:00'),
                143.08,
            ],
        ];
    }

    /**
     * @return PriceService
     */
    private function getService()
    {
        return new PriceService(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('shopware_storefront.context_service')
        );
    }

    private function installLiveShopping()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShoppingPriceTest.sql');

        $this->execSql($sql);
    }
}
