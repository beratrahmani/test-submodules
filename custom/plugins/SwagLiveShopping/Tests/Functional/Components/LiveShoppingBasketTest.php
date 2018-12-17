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

namespace Shopware\SwagLiveShopping\Tests\Functional\Components;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price;
use Shopware\Models\Customer\Group;
use Shopware\Models\Order\Basket;
use SwagLiveShopping\Components\LiveShoppingBasketInterface;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class LiveShoppingBasketTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_getNewBasketItem()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();

        $result = $liveShoppingBasket->getNewBasketItem();

        $this->assertInstanceOf(Basket::class, $result);
    }

    public function test_getVariantCreateData()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        $result = $liveShoppingBasket->getVariantCreateData($variant, 1);

        $expectedResult = [
            'articleId' => 153,
            'orderNumber' => 'SW10153.1',
            'quantity' => 1,
        ];

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_getAttributeCreateData()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $result = $liveShoppingBasket->getAttributeCreateData(new Detail(), 1);

        $expectedResult = [
            'attribute1' => null,
            'attribute2' => null,
            'attribute3' => null,
            'attribute4' => null,
            'attribute5' => null,
            'attribute6' => null,
        ];

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_getVariantUpdateData()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        $result = $liveShoppingBasket->getVariantUpdateData($variant, 5);

        $expectedResult = [
            'quantity' => 5,
        ];

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_getAttributeUpdateData()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $result = $liveShoppingBasket->getAttributeUpdateData(new Detail(), 5);

        $this->assertEmpty($result);
    }

    public function test_updateItem()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingBasketProduct();

        $data = [
            'quantity' => 12,
        ];

        $liveShoppingBasket->updateItem(672, $data, new Detail(), 0, []);

        /** @var Basket $basket */
        $basket = $this->getContainer()->get('models')->find(Basket::class, 672);

        $this->assertInstanceOf(Basket::class, $basket);
        $this->assertSame(170, $basket->getArticleId());
        $this->assertSame(12, $basket->getQuantity());
    }

    public function test_getItem_as_array()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingBasketProduct();

        $result = $liveShoppingBasket->getItem(672);

        $this->assertTrue(is_array($result));
        $this->assertSame('SW10170', $result['orderNumber']);
    }

    public function test_getItem_as_object()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingBasketProduct();

        /** @var Basket $result */
        $result = $liveShoppingBasket->getItem(672, AbstractQuery::HYDRATE_OBJECT);

        $this->assertInstanceOf(Basket::class, $result);
        $this->assertSame('SW10170', $result->getOrderNumber());
    }

    public function test_getVariantName()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        $result = $liveShoppingBasket->getVariantName($variant, 1);

        $expectedResult = 'Flip Flops, in mehreren Farben verfügbar blau / 39/40';

        $this->assertSame($expectedResult, $result);
    }

    public function test_getProductId()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        $result = $liveShoppingBasket->getProductId($variant, 1);

        $this->assertSame(153, $result);
    }

    public function test_getNumber()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        $result = $liveShoppingBasket->getNumber($variant, 1);

        $this->assertSame('SW10153.1', $result);
    }

    public function test_getShippingFree()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        $result = $liveShoppingBasket->getShippingFree($variant, 1);

        $this->assertFalse((bool) $result);
    }

    public function test_getVariantPrice()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        $result = $liveShoppingBasket->getVariantPrice($variant, 1);

        $expectedResult = [
            'gross' => 6.99,
            'net' => 5.8739495798319,
        ];

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_getNetAndGrossPriceForVariantPrice()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $price = new Price();
        $price->setPrice(99.99);

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        $result = $liveShoppingBasket->getNetAndGrossPriceForVariantPrice($price, $variant, []);

        $expectedResult = [
            'gross' => 118.99,
            'net' => 99.99,
        ];

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_getPricesForCustomerGroup()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        $result = $liveShoppingBasket->getPricesForCustomerGroup($variant, 'H', 'EK');

        $expectedResult = [
            'articleId' => 153,
            'articleDetailsId' => 322,
            'customerGroupKey' => 'EK',
            'price' => 5.8739495798319,
        ];

        $this->assertArraySubset($expectedResult, $result[0]);
    }

    public function test_getPriceQueryBuilder()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();

        $result = $liveShoppingBasket->getPriceQueryBuilder();

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function test_getCurrentCustomerGroup()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();

        /** @var Group $result */
        $result = $liveShoppingBasket->getCurrentCustomerGroup();

        $this->assertInstanceOf(Group::class, $result);
        $this->assertSame('EK', $result->getKey());
    }

    public function test_getPriceForQuantity()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();

        $price = new Price();
        $price->setPrice(112.99);
        $price->setFrom(0);
        $price->setTo(10);

        $price2 = new Price();
        $price2->setPrice(100);
        $price2->setFrom(11);
        $price2->setTo(20);

        /** @var Group $result */
        $result = $liveShoppingBasket->getPriceForQuantity([$price, $price2], 12, new Detail());

        $this->assertInstanceOf(Price::class, $result);
        $this->assertSame($price2, $result);
    }

    public function test_getEsdFlag()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);
        $result = $liveShoppingBasket->getEsdFlag($variant, 1);

        $this->assertSame(0, $result);
    }

    public function test_getVariantByOrderNumber()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingVariantProduct();

        $result = $liveShoppingBasket->getVariantByOrderNumber('SW10153.1');

        $this->assertInstanceOf(Detail::class, $result);
        $this->assertSame(322, $result->getId());
    }

    public function test_validateProduct()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingProductForCustomerGroupH();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(407);

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setParam('customProductsHash', '');
        Shopware()->Container()->get('front')->setRequest($request);

        $result = $liveShoppingBasket->validateProduct($variant, 1);

        $expectedResult = [
            'success' => true,
        ];

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_isVariantInStock_should_be_true()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingProductForCustomerGroupH();

        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(407);

        $result = $liveShoppingBasket->isVariantInStock($variant, 1);

        $this->assertTrue($result);
    }

    public function test_isVariantInStock_should_be_false()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingProductForCustomerGroupH();

        /** @var Detail $variant */
        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(407);
        $variant->getArticle()->setLastStock(true);
        $variant->setInStock(10);

        $result = $liveShoppingBasket->isVariantInStock($variant, 12);

        $this->assertFalse($result);
    }

    public function test_getSummarizedQuantityOfVariant()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();
        $this->installLiveShoppingBasketProduct();

        Shopware()->Session()->sessionId = 'sessionId';
        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(394);

        $result = $liveShoppingBasket->getSummarizedQuantityOfVariant($variant, 1);

        $this->assertSame('1', $result);
    }

    public function test_isCustomerGroupAllowed_should_be_true()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();

        $customerGroup = new Group();
        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(394);
        $result = $liveShoppingBasket->isCustomerGroupAllowed($variant, $customerGroup, []);

        $this->assertTrue($result);
    }

    public function test_isCustomerGroupAllowed_should_be_false()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();

        $customerGroup = Shopware()->Container()->get('models')->getRepository(Group::class)->find(2);
        /** @var Detail $variant */
        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(394);
        $variant->getArticle()->setCustomerGroups(new ArrayCollection([$customerGroup]));
        $result = $liveShoppingBasket->isCustomerGroupAllowed($variant, $customerGroup, []);

        $this->assertFalse($result);
    }

    public function test_createItem()
    {
        $liveShoppingBasket = $this->getLiveShoppingBasket();

        $data = [
            'customerId' => 1,
            'partnerId' => 0,
            'articleID' => 178,
            'orderNumber' => 'SW10170',
            'quantity' => 10,
            'taxRate' => 10.00,
            'sessionId' => 'sessionId',
            'date' => '01.01.1970 00:00:00',
        ];

        /** @var Detail $variant */
        $variant = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(394);

        $result = $liveShoppingBasket->createItem($data, $variant, 10, []);
        /** @var Basket $result2 */
        $result2 = Shopware()->Container()->get('models')->getRepository(Basket::class)->find($result);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(Basket::class, $result2);
        $this->assertSame($data['orderNumber'], $result2->getOrderNumber());
    }

    private function installLiveShoppingProductForCustomerGroupH()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShoppingProductCustomerGroupH.sql');
        $databaseConnection->exec($sql);
    }

    private function installLiveShoppingBasketProduct()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShoppingProductInBasket.sql');
        $databaseConnection->exec($sql);
    }

    private function installLiveShoppingVariantProduct()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShopingVariantProduct.sql');
        $databaseConnection->exec($sql);
    }

    private function registerNamespace()
    {
        Shopware()->Loader()->registerNamespace('Shopware_Components', __DIR__ . '/../../../' . 'Components/');
    }

    /**
     * @return LiveShoppingBasketInterface
     */
    private function getLiveShoppingBasket()
    {
        $this->registerNamespace();

        return $this->getContainer()->get('swag_liveshopping.live_shopping_basket');
    }
}
