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

namespace SwagPromotion\Tests\Functional;

use Shopware\Components\Test\Plugin\TestCase;
use SwagPromotion\Components\DataProvider\ProductDataProvider;
use SwagPromotion\Components\Promotion\ProductChunker\CheapestProductChunker;
use SwagPromotion\Components\Promotion\ProductChunker\ProductChunkerRegistry;
use SwagPromotion\Components\Promotion\ProductStacker\ArticleProductStacker;
use SwagPromotion\Components\Promotion\ProductStacker\DetailProductStacker;
use SwagPromotion\Components\Promotion\ProductStacker\GlobalProductStacker;

/**
 * @small
 */
class ProductStackerTest extends TestCase
{
    public static function tearDownAfterClass()
    {
        Shopware()->Modules()->Basket()->sDeleteBasket();

        parent::tearDownAfterClass();
    }

    public function testDetailProductStacker()
    {
        list($basket, $products) = $this->fillBasket(['SW10010' => 10, 'SW10009' => 100]);
        $stacker = new DetailProductStacker();
        $stacks = $stacker->getStack($products, 2, 10);
        $this->assertEquals(10, count($stacks), 'Stack number does not match');
        $this->assertEquals(2, count($stacks[0]), 'Stack item number does not match');
    }

    public function testArticleProductStacker()
    {
        list($basket, $products) = $this->fillBasket(['SW10002.1' => 1, 'SW10002.2' => 1, 'SW10010' => 2]);
        $stacker = new ArticleProductStacker($this->getChunkerRegistry());
        $stacks = $stacker->getStack($products, 2, 10);
        $this->assertEquals(2, count($stacks), 'Stack number does not match');
        $this->assertEquals(2, count($stacks[0]), 'Stack item number does not match');
    }

    public function testGlobalProductStacker()
    {
        list($basket, $products) = $this->fillBasket(['SW10002.1' => 1, 'SW10010' => 1]);
        $stacker = new GlobalProductStacker($this->getChunkerRegistry());
        $stacks = $stacker->getStack($products, 2, 10);
        $this->assertEquals(1, count($stacks), 'Stack number does not match');
        $this->assertEquals(2, count($stacks[0]), 'Stack item number does not match');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The chunker for foo is not registered
     */
    public function testChunkerNotAvailable()
    {
        list($basket, $products) = $this->fillBasket(['SW10002.1' => 1, 'SW10010' => 1]);
        $stacker = new GlobalProductStacker($this->getChunkerRegistry());
        $stacks = $stacker->getStack($products, 2, 10, 'foo');
    }

    private function getChunkerRegistry()
    {
        return new ProductChunkerRegistry([
            'cheapest' => new CheapestProductChunker(),
        ]);
    }

    /**
     * @param $products
     *
     * @internal param $number
     *
     * @return array
     */
    private function fillBasket($products)
    {
        Shopware()->Modules()->Basket()->sDeleteBasket();
        foreach ($products as $number => $quantity) {
            if (!Shopware()->Modules()->Basket()->sAddArticle($number, $quantity)) {
                throw new \RuntimeException("Could not add $number to basket");
            }
        }
        $basket = Shopware()->Modules()->Basket()->sGetBasket();
        $dataProvider = new ProductDataProvider(Shopware()->Db());
        $products = $dataProvider->get($products);

        return [$basket, $products];
    }
}
