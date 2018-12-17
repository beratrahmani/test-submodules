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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_RequestHttp;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use SwagLiveShopping\Components\LiveShoppingInterface;
use SwagLiveShopping\Models\LiveShopping as LiveShoppingModel;
use SwagLiveShopping\Models\Price;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class LiveShoppingTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_getActiveLiveShoppingForProduct_should_be_empty()
    {
        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->getActiveLiveShoppingForProduct(1);

        $this->assertEmpty($result);
    }

    public function test_getActiveLiveShoppingForProduct_should_not_empty()
    {
        $this->installLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        /** @var LiveShoppingModel $result */
        $result = $liveShopping->getActiveLiveShoppingForProduct(178);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(LiveShoppingModel::class, $result);
    }

    public function test_getActiveLiveShoppingForVariant()
    {
        $this->installLiveShoppingVariantProduct();

        $detail = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->getActiveLiveShoppingForVariant($detail);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(LiveShoppingModel::class, $result);
    }

    public function test_validateLiveShopping_no_liveShopping()
    {
        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $expectedResult = ['noLiveShoppingDetected' => true];
        $result = $liveShopping->validateLiveShopping(new \stdClass());

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_validateLiveShopping_should_be_inactive()
    {
        $this->installInactiveLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10002);

        $expectedResult = ['noMoreActive' => true];
        $result = $liveShopping->validateLiveShopping($liveShoppingProduct);

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_validateLiveShopping_false_customer_group()
    {
        $this->installLiveShoppingWithFalseCustomerGroup();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10003);

        $expectedResult = ['notForCurrentCustomerGroup' => true];
        $result = $liveShopping->validateLiveShopping($liveShoppingProduct);

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_validateLiveShopping_false_price()
    {
        $this->installLiveShoppingProductWithFalsePrice();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10004);

        $expectedResult = ['notForCurrentCustomerGroup' => true];
        $result = $liveShopping->validateLiveShopping($liveShoppingProduct);

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_validateLiveShopping_not_in_stock()
    {
        $this->installLiveShoppingProductOutOfStock();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(30001);

        $expectedResult = ['noStock' => true];
        $result = $liveShopping->validateLiveShopping($liveShoppingProduct);

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_validateLiveShopping_not_for_shop()
    {
        $this->installLiveShoppingProductNotForShop();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10005);

        $expectedResult = ['notForShop' => true];
        $result = $liveShopping->validateLiveShopping($liveShoppingProduct);

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_validateLiveShopping_is_valid()
    {
        $this->installLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10001);

        $result = $liveShopping->validateLiveShopping($liveShoppingProduct);

        $this->assertTrue($result);
    }

    public function test_getActiveLiveShoppingById()
    {
        $this->installLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());
        Shopware()->Front()->Request()->setParam('productId', 178);

        /** @var LiveShoppingModel $result */
        $result = $liveShopping->getActiveLiveShoppingById(10001);

        $this->assertNotEmpty($result);
        $this->assertSame('08154711', $result->getNumber());
    }

    public function test_getLiveShoppingArrayData_should_be_empty()
    {
        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $result = $liveShopping->getLiveShoppingArrayData(new \stdClass());

        $this->assertEmpty($result);
    }

    public function test_getLiveShoppingData_should_not_be_empty()
    {
        $this->installLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        /** @var LiveShoppingModel $liveShoppingProduct */
        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10001);
        $price = new Price();
        $price->setPrice(10.00);
        $liveShoppingProduct->setUpdatedPrices(new ArrayCollection([$price]));

        $result = $liveShopping->getLiveShoppingArrayData($liveShoppingProduct);

        $this->assertNotEmpty($result);
        $this->assertSame('08154711', $result['number']);
    }

    public function test_getCurrentTaxRate()
    {
        $this->installLiveShoppingProduct();

        $product = Shopware()->Container()->get('models')->getRepository(Article::class)->find(178);

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $result = $liveShopping->getCurrentTaxRate($product);

        $this->assertSame('19.00', $result);
    }

    public function test_getCurrentCurrencyFactor()
    {
        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->getCurrentCurrencyFactor();

        $this->assertSame(1.00, $result);
    }

    public function test_displayNetPrices_should_be_false()
    {
        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->displayNetPrices();

        $this->assertFalse($result);
    }

    public function test_displayNetPrices_should_be_true()
    {
        $session = Shopware()->Container()->get('session');
        $data = ['groupkey' => 'H'];
        $session->sUserGroupData = $data;

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->displayNetPrices();

        $this->assertTrue($result);
    }

    public function test_use_net_prices_in_basket_should_be_false()
    {
        $session = Shopware()->Container()->get('session');
        $data = ['groupkey' => 'EK'];
        $session->sUserGroupData = $data;

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->useNetPriceInBasket();

        $this->assertFalse($result);
    }

    public function test_use_net_prices_in_basket_should_be_true()
    {
        $session = Shopware()->Container()->get('session');
        $data = ['groupkey' => 'H'];
        $session->sUserGroupData = $data;

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->useNetPriceInBasket();

        $this->assertTrue($result);
    }

    public function test_getLiveShoppingProductName_should_be_empty()
    {
        $this->installLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $result = $liveShopping->getLiveShoppingProductName(new \stdClass());

        $this->assertEmpty($result);
    }

    public function test_getLiveShoppingProductName()
    {
        $this->installLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        /** @var LiveShoppingModel $liveShoppingProduct */
        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10001);

        $result = $liveShopping->getLiveShoppingProductName($liveShoppingProduct);

        $this->assertSame('Strandtuch "Ibiza"', $result);
    }

    public function test_isLiveShoppingDateActive_should_be_empty()
    {
        $this->installLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $result = $liveShopping->isLiveShoppingDateActive(new \stdClass());

        $this->assertFalse($result);
    }

    public function test_isLiveShoppingDateActive_should_be_true()
    {
        $this->installLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        /** @var LiveShoppingModel $liveShoppingProduct */
        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10001);

        $result = $liveShopping->isLiveShoppingDateActive($liveShoppingProduct);

        $this->assertTrue($result);
    }

    public function test_getBasketLiveShoppingProducts()
    {
        $this->installBasketLiveShoppingArticle();

        Shopware()->Session()->sessionId = 'sessionId';

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        /** @var LiveShoppingModel[] $result */
        $result = $liveShopping->getBasketLiveShoppingProducts();

        $this->assertInstanceOf(LiveShoppingModel::class, $result[672]);
        $this->assertSame('08154712', $result[672]->getNumber());
    }

    public function test_getProductByLiveShopping_get_productId_from_request()
    {
        $this->installLiveShoppingProduct();

        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());
        Shopware()->Front()->Request()->setParam('productId', 407);

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        /** @var LiveShoppingModel $liveShoppingProduct */
        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10001);

        /** @var Detail $result */
        $result = $liveShopping->getProductByLiveShopping($liveShoppingProduct);

        $this->assertInstanceOf(Detail::class, $result);
        $this->assertSame(178, $result->getArticleId());
    }

    public function test_getProductByLiveShopping_get_productId_from_liveShopping()
    {
        $this->installLiveShoppingProduct();

        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        /** @var LiveShoppingModel $liveShoppingProduct */
        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(10001);

        /** @var Detail $result */
        $result = $liveShopping->getProductByLiveShopping($liveShoppingProduct);

        $this->assertInstanceOf(Detail::class, $result);
        $this->assertSame(178, $result->getArticleId());
    }

    public function test_decreaseLiveShoppingStock()
    {
        $this->installLiveShoppingProductOutOfStock();

        $sql = '
            UPDATE s_articles_lives SET max_quantity = 100 WHERE id =  30001;
        ';

        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $connection->exec($sql);

        /** @var LiveShoppingModel $liveShoppingProduct */
        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(30001);

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $liveShopping->decreaseLiveShoppingStock($liveShoppingProduct, 50);

        $sql = '
            SELECT max_quantity FROM s_articles_lives WHERE id = 30001;
        ';

        $result = $connection->fetchColumn($sql);

        $this->assertSame('50', $result);
    }

    public function test_isVariantAllowed_should_be_false()
    {
        $this->installLiveShoppingVariantProduct();

        /** @var LiveShoppingModel $liveShoppingProduct */
        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(20001);

        $productDetail = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(323);

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->isVariantAllowed($liveShoppingProduct, $productDetail);

        $this->assertFalse($result);
    }

    public function test_isVariantAllowed_should_be_true()
    {
        $this->installLiveShoppingVariantProduct();

        /** @var LiveShoppingModel $liveShoppingProduct */
        $liveShoppingProduct = Shopware()->Container()->get('models')->getRepository(LiveShoppingModel::class)->find(20001);

        $productDetail = Shopware()->Container()->get('models')->getRepository(Detail::class)->find(322);

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->isVariantAllowed($liveShoppingProduct, $productDetail);

        $this->assertTrue($result);
    }

    public function test_getLiveShoppingByNumber()
    {
        $this->installLiveShoppingProduct();

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');

        $result = $liveShopping->getLiveShoppingByNumber('SW10178');

        $this->assertNotEmpty($result);
        $this->assertCount(2, $result);
        $this->assertSame('08154711', $result[0]['number']);
    }

    public function test_haveVariantsLiveShopping_should_be_true()
    {
        $this->installLiveShoppingVariantProduct();

        /** @var ContextService $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        $products = [
            new ListProduct(153, 322, 'SW10153.1'),
            new ListProduct(153, 323, 'SW10153.2'),
            new ListProduct(153, 324, 'SW10153.3'),
            new ListProduct(153, 325, 'SW10153.4'),
            new ListProduct(153, 326, 'SW10153.5'),
            new ListProduct(153, 327, 'SW10153.6'),
            new ListProduct(153, 328, 'SW10153.7'),
            new ListProduct(153, 329, 'SW10153.8'),
            new ListProduct(153, 330, 'SW10153.9'),
            new ListProduct(153, 331, 'SW10153.10'),
            new ListProduct(153, 332, 'SW10153.11'),
            new ListProduct(153, 333, 'SW10153.12'),
            new ListProduct(153, 334, 'SW10153.13'),
            new ListProduct(153, 335, 'SW10153.14'),
            new ListProduct(153, 336, 'SW10153.15'),
            new ListProduct(153, 337, 'SW10153.16'),
            new ListProduct(153, 338, 'SW10153.17'),
            new ListProduct(153, 339, 'SW10153.18'),
            new ListProduct(153, 340, 'SW10153.19'),
            new ListProduct(153, 341, 'SW10153.20'),
            new ListProduct(153, 342, 'SW10153.21'),
        ];

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->haveVariantsLiveShopping($products, $contextService->getShopContext());

        $this->assertCount(count($products), $result);
    }

    public function test_haveVariantsLiveShopping_should_be_empty()
    {
        $this->installLiveShoppingVariantProduct();

        /** @var ContextService $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        $products = [
            new ListProduct(178, 407, 'SW10178'),
        ];

        /** @var LiveShoppingInterface $liveShopping */
        $liveShopping = $this->getContainer()->get('swag_liveshopping.live_shopping');
        $result = $liveShopping->haveVariantsLiveShopping($products, $contextService->getShopContext());

        $this->assertEmpty($result);
    }

    private function installBasketLiveShoppingArticle()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShoppingProductInBasket.sql');
        $databaseConnection->exec($sql);
    }

    private function installLiveShoppingProductNotForShop()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShoppingProductNotForShop.sql');
        $databaseConnection->exec($sql);
    }

    private function installLiveShoppingProductOutOfStock()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShoppingProductOutOfStock.sql');
        $databaseConnection->exec($sql);
    }

    private function installLiveShoppingProductWithFalsePrice()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShoppingProductFalsePrice.sql');
        $databaseConnection->exec($sql);
    }

    private function installLiveShoppingWithFalseCustomerGroup()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShoppingProductCustomerGroupH.sql');
        $databaseConnection->exec($sql);
    }

    private function installLiveShoppingVariantProduct()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShopingVariantProduct.sql');
        $databaseConnection->exec($sql);
    }

    private function installInactiveLiveShoppingProduct()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/InactiveLiveShoppingProduct.sql');
        $databaseConnection->exec($sql);
    }

    private function installLiveShoppingProduct()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = Shopware()->Container()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/LiveShoppingProduct.sql');
        $databaseConnection->exec($sql);
    }
}
