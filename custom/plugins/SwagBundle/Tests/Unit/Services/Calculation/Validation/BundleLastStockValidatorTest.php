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

namespace SwagBundle\Tests\Unit\Services\Calculation\Validation;

use PHPUnit\Framework\TestCase;
use Shopware\Models\Article\Detail;
use SwagBundle\Components\BundleBasket;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\Calculation\Validation\BundleLastStockValidator;

class BundleLastStockValidatorTest extends TestCase
{
    const LAST_STOCK_DISABLED = 0;
    const LAST_STOCK_ENABLED = 1;
    const NO_INSTOCK = 0;

    const EQUAL_INSTOCK = 10;

    public function test_it_can_be_created()
    {
        $bundleLastStockValidator = new BundleLastStockValidator(
            $this->createMock(BundleBasket::class)
        );
        $this->assertInstanceOf(BundleLastStockValidator::class, $bundleLastStockValidator);
    }

    public function test_it_should_be_valid_if_lastStock_is_disabled_on_main_product()
    {
        $detail = new Detail();
        $detail->setLastStock(self::LAST_STOCK_DISABLED);

        $bundleBasketComponentMock = $this->createMock(BundleBasket::class);

        $bundleLastStockValidator = new BundleLastStockValidator($bundleBasketComponentMock);

        $result = $bundleLastStockValidator->validate($detail, new Bundle());

        $this->assertTrue($result);
    }

    public function test_it_should_be_valid_if_instock_is_available_and_is_higher_than_the_given_quantities()
    {
        $detail = new Detail();
        $detail->setLastStock(self::LAST_STOCK_ENABLED);
        $detail->setInStock(1000);

        $bundle = new Bundle();
        $bundle->setQuantity(100);

        $bundleBasketComponentMock = $this->createMock(BundleBasket::class);
        $bundleBasketComponentMock
            ->expects($this->once())
            ->method('getSummarizedQuantityOfVariant')
            ->with($detail)
            ->willReturn(10);

        $bundleLastStockValidator = new BundleLastStockValidator($bundleBasketComponentMock);

        $result = $bundleLastStockValidator->validate($detail, $bundle);

        $this->assertTrue($result);
    }

    public function test_it_should_be_invalid_if_lastStock_is_enabled_and_basketQuantity_is_equal_to_inStock()
    {
        $detail = new Detail();
        $detail->setLastStock(self::LAST_STOCK_ENABLED);
        $detail->setInStock(self::EQUAL_INSTOCK);

        $bundle = new Bundle();
        $bundleBasketComponentMock = $this->createMock(BundleBasket::class);
        $bundleBasketComponentMock
            ->expects($this->once())
            ->method('getSummarizedQuantityOfVariant')
            ->with($detail)
            ->willReturn(self::EQUAL_INSTOCK);

        $bundleLastStockValidator = new BundleLastStockValidator($bundleBasketComponentMock);
        $result = $bundleLastStockValidator->validate($detail, $bundle);

        $this->assertFalse($result);
    }

    public function test_it_should_be_invalid_if_bundle_quantity_is_lower_than_basket_quantity()
    {
        $detail = new Detail();
        $detail->setLastStock(self::LAST_STOCK_ENABLED);
        $detail->setInStock(10);

        $bundle = new Bundle();
        $bundle->setLimited(true);
        $bundle->setQuantity(1);

        $bundleBasketComponentMock = $this->createMock(BundleBasket::class);
        $bundleBasketComponentMock
            ->expects($this->once())
            ->method('getSummarizedQuantityOfVariant')
            ->with($detail)
            ->willReturn(2);

        $bundleLastStockValidator = new BundleLastStockValidator($bundleBasketComponentMock);
        $result = $bundleLastStockValidator->validate($detail, $bundle);

        $this->assertFalse($result);
    }

    public function test_it_should_valid_if_bundle_has_no_limitation()
    {
        $detail = new Detail();
        $detail->setLastStock(self::LAST_STOCK_ENABLED);
        $detail->setInStock(10);

        $bundle = new Bundle();
        $bundle->setLimited(false);
        $bundle->setQuantity(1);

        $bundleBasketComponentMock = $this->createMock(BundleBasket::class);
        $bundleBasketComponentMock
            ->expects($this->once())
            ->method('getSummarizedQuantityOfVariant')
            ->with($detail)
            ->willReturn(2);

        $bundleLastStockValidator = new BundleLastStockValidator($bundleBasketComponentMock);
        $result = $bundleLastStockValidator->validate($detail, $bundle);

        $this->assertTrue($result);
    }

    public function test_it_should_invalid_if_basket_quantity_is_0_and_product_is_out_of_stock()
    {
        $detail = new Detail();
        $detail->setLastStock(self::LAST_STOCK_ENABLED);
        $detail->setInStock(0);

        $bundle = new Bundle();
        $bundle->setLimited(false);

        $bundleBasketComponentMock = $this->createMock(BundleBasket::class);
        $bundleBasketComponentMock
            ->expects($this->once())
            ->method('getSummarizedQuantityOfVariant')
            ->with($detail)
            ->willReturn(self::NO_INSTOCK);

        $bundleLastStockValidator = new BundleLastStockValidator($bundleBasketComponentMock);

        $result = $bundleLastStockValidator->validate($detail, $bundle);

        $this->assertFalse($result);
    }

    public function test_it_should_be_invalid_if_basket_is_0_and_bundle_quantity_is_0()
    {
        $detail = new Detail();
        $detail->setLastStock(self::LAST_STOCK_DISABLED);

        $bundle = new Bundle();
        $bundle->setLimited(true);
        $bundle->setQuantity(self::NO_INSTOCK);

        $bundleBasketComponentMock = $this->createMock(BundleBasket::class);
        $bundleBasketComponentMock
            ->expects($this->once())
            ->method('getSummarizedQuantityOfVariant')
            ->with($detail)
            ->willReturn(0);

        $bundleLastStockValidator = new BundleLastStockValidator($bundleBasketComponentMock);

        $result = $bundleLastStockValidator->validate($detail, $bundle);

        $this->assertFalse($result);
    }
}
