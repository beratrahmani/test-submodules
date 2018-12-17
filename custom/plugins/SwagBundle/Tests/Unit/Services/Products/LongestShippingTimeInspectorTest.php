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

namespace SwagBundle\Tests\Unit\Services\Products;

use DateTime;
use PHPUnit\Framework\TestCase;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\Products\LongestShippingTimeInspector;

class LongestShippingTimeInspectorTest extends TestCase
{
    public function test_it_can_be_created()
    {
        $longestShippingTimeInspector = new LongestShippingTimeInspector();

        $this->assertInstanceOf(LongestShippingTimeInspector::class, $longestShippingTimeInspector);
    }

    public function test_determineLongestShippingProduct_should_determine_product_by_non_availability()
    {
        $products = [
            ['instock' => -1, 'name' => 'product without instock'],
            ['instock' => 100, 'name' => 'product with valid instock'],
        ];

        $bundle = new Bundle();
        $longestShippingTimeInspector = new LongestShippingTimeInspector();

        $longestShippingTimeInspector->determineLongestShippingProduct($products, $bundle);

        $this->assertEquals('product without instock', $bundle->getLongestShippingTimeProduct()['name']);
    }

    public function test_determineLongestShippingProduct_should_determine_by_non_stocked_products()
    {
        $products = [
            ['instock' => -1, 'shippingtime' => 5, 'esd' => false, 'name' => 'Product without instock'],
        ];

        $bundle = new Bundle();

        $longestShippingTimeInspector = new LongestShippingTimeInspector('2017-01-01');

        $longestShippingTimeInspector->determineLongestShippingProduct($products, $bundle);

        $this->assertEquals('Product without instock', $bundle->getLongestShippingTimeProduct()['name']);
    }

    public function test_determineLongestShippingProduct_should_determine_by_non_stocked_products_with_release_date()
    {
        $products = [
            [
                'instock' => -1,
                'shippingtime' => 5,
                'sReleaseDate' => new DateTime('2017-01-01'),
                'esd' => false,
                'name' => 'Product without instock',
            ],
            [
                'instock' => -1,
                'shippingtime' => 5,
                'sReleaseDate' => new DateTime('2017-01-02'),
                'esd' => false,
                'name' => 'Product without instock',
            ],
        ];

        $bundle = new Bundle();

        $longestShippingTimeInspector = new LongestShippingTimeInspector('2017-01-02');

        $longestShippingTimeInspector->determineLongestShippingProduct($products, $bundle);

        $this->assertEquals('Product without instock', $bundle->getLongestShippingTimeProduct()['name']);
    }

    public function test_determineLongestShippingProduct_should_determine_product_by_stocked()
    {
        $products = [
            ['instock' => 100, 'esd' => false, 'name' => 'Product with instock'],
        ];

        $bundle = new Bundle();

        $longestShippingTimeInspector = new LongestShippingTimeInspector();

        $longestShippingTimeInspector->determineLongestShippingProduct($products, $bundle);

        $this->assertEquals('Product with instock', $bundle->getLongestShippingTimeProduct()['name']);
    }

    public function test_determineLongestShippingProduct_should_determine_by_esd()
    {
        $products = [
            ['esd' => true, 'name' => 'Esd product'],
        ];

        $bundle = new Bundle();

        $longestShippingTimeInspector = new LongestShippingTimeInspector();

        $longestShippingTimeInspector->determineLongestShippingProduct($products, $bundle);

        $this->assertEquals('Esd product', $bundle->getLongestShippingTimeProduct()['name']);
    }

    public function test_determineLongestShippingProduct_should_not_determine_on_empty_array()
    {
        $products = [];
        $bundle = new Bundle();

        $longestShippingTimeInspector = new LongestShippingTimeInspector();

        $longestShippingTimeInspector->determineLongestShippingProduct($products, $bundle);

        $this->assertEquals('', $bundle->getLongestShippingTimeProduct());
    }
}
