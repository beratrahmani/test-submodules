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

namespace SwagBundle\Tests\Unit\Services\Calculation;

use PHPUnit\Framework\TestCase;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Customer\Group;
use Shopware\Models\Tax\Tax;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Models\Bundle;
use SwagBundle\Models\Price;
use SwagBundle\Services\Calculation\BundlePriceCalculator;

class BundlePriceCalculatorTest extends TestCase
{
    public function test_it_can_be_created()
    {
        $bundlePriceCalculator = $this->createBundlePriceCalculator();
        $this->assertInstanceOf(BundlePriceCalculator::class, $bundlePriceCalculator);
    }

    public function test_it_should_throw_exception_if_totalPrice_is_not_calculated()
    {
        $bundlePriceCalculator = $this->createBundlePriceCalculator();
        $reflection = new \ReflectionClass(get_class($bundlePriceCalculator));
        $method = $reflection->getMethod('calculate');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $method->invokeArgs($bundlePriceCalculator, [new Bundle(), [], 0.0]);
    }

    public function test_it_should_calculate_percentage_discount_of_10percent()
    {
        $bundlePriceCalculator = $this->createBundlePriceCalculator();
        $reflection = new \ReflectionClass(get_class($bundlePriceCalculator));
        $method = $reflection->getMethod('calculate');
        $method->setAccessible(true);

        $bundle = new Bundle();
        $bundle->setTotalPrice(['gross' => 119, 'net' => 100]);
        $bundle->setDiscountType(BundleComponentInterface::PERCENTAGE_DISCOUNT);

        $price = new Price();
        $price->setPrice(10.0);
        $price->setBundle($bundle);

        $customerGroup = new Group();
        $customerGroup->setTax(true);
        $price->setCustomerGroup($customerGroup);

        $method->invokeArgs($bundlePriceCalculator, [$bundle, [$price], 0.0]);

        $this->assertEquals(90.0, $price->getNetPrice());
        $this->assertEquals(107.1, $price->getGrossPrice());
        $this->assertEquals('107,10', $price->getDisplayPrice());
    }

    public function test_it_should_calculate_absolute_discount_of_10()
    {
        $bundlePriceCalculator = $this->createBundlePriceCalculator();
        $reflection = new \ReflectionClass(get_class($bundlePriceCalculator));
        $method = $reflection->getMethod('calculate');
        $method->setAccessible(true);

        $tax = new Tax();
        $tax->setTax(19.0);

        $product = new Product();
        $product->setTax($tax);

        $bundle = new Bundle();
        $bundle->setDiscountType(BundleComponentInterface::ABSOLUTE_DISCOUNT);
        $bundle->setTotalPrice(['gross' => 119, 'net' => 100]);
        $bundle->setArticle($product);

        $price = new Price();
        $price->setBundle($bundle);
        $price->setPrice(10.0);

        $customerGroup = new Group();
        $customerGroup->setTax(true);
        $price->setCustomerGroup($customerGroup);

        $method->invokeArgs($bundlePriceCalculator, [$bundle, [$price], 1]);

        $this->assertEquals(11.9, $price->getGrossPrice());
        $this->assertEquals(10.0, $price->getNetPrice());
        $this->assertEquals('11,90', $price->getDisplayPrice());
    }

    public function test_it_should_calculate_currencyFactor_for_absolute_discounts()
    {
        $bundlePriceCalculator = $this->createBundlePriceCalculator();
        $reflection = new \ReflectionClass(get_class($bundlePriceCalculator));
        $method = $reflection->getMethod('calculate');
        $method->setAccessible(true);

        $tax = new Tax();
        $tax->setTax(19.0);

        $product = new Product();
        $product->setTax($tax);

        $bundle = new Bundle();
        $bundle->setDiscountType(BundleComponentInterface::ABSOLUTE_DISCOUNT);
        $bundle->setTotalPrice(['gross' => 119, 'net' => 100]);
        $bundle->setArticle($product);

        $price = new Price();
        $price->setBundle($bundle);
        $price->setPrice(10.0);

        $customerGroup = new Group();
        $customerGroup->setTax(true);
        $price->setCustomerGroup($customerGroup);

        $method->invokeArgs($bundlePriceCalculator, [$bundle, [$price], 2]);

        $this->assertEquals(23.800000000000001, $price->getGrossPrice());
        $this->assertEquals(10.0, $price->getNetPrice());
        $this->assertEquals('23,80', $price->getDisplayPrice());
    }

    /**
     * @return BundlePriceCalculator
     */
    private function createBundlePriceCalculator()
    {
        return new BundlePriceCalculator(
            Shopware()->Container()->get('swag_bundle.dependencies.provider'),
            Shopware()->Container()->get('swag_bundle.calculation.calculation_repository'),
            Shopware()->Container()->get('models')
        );
    }
}
