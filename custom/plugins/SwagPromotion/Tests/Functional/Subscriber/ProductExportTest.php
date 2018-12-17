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

namespace  SwagPromotion\Tests\Functional\Subscriber;

use Shopware\Bundle\StoreFrontBundle\Service\Core\ListProductService;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\Shop;
use SwagPromotion\Components\Listing\ListProductDecorator;
use SwagPromotion\Components\ProductExport\PromotionExport;
use SwagPromotion\Struct\Promotion;
use SwagPromotion\Subscriber\ProductExport;
use SwagPromotion\Tests\Helper\PromotionFactory;
use SwagPromotion\Tests\PromotionTestCase;

class ProductExportTest extends PromotionTestCase
{
    const DUMMY_CONFIG = ['languageID' => 1, 'customergroupID' => 1];
    const DUMMY_PRODUCTS = [['ordernumber' => 'SW10003', 'price' => 14.95]];

    public function test_construct()
    {
        $subscriber = $this->getSubscriber();
        $this->assertInstanceOf(ProductExport::class, $subscriber);
    }

    public function test_onFilterProductExport_without_config()
    {
        $args = new \Enlight_Event_EventArgs();
        $args->setReturn(self::DUMMY_PRODUCTS);

        $subscriber = $this->getSubscriber();
        $subscriber->onFilterProductExport($args);

        $this->assertArraySubset(self::DUMMY_PRODUCTS, $args->getReturn());
    }

    public function test_onFilterProductExport_will_update_prices_without_customer_group()
    {
        $args = new \Enlight_Event_EventArgs();
        $args->setReturn(self::DUMMY_PRODUCTS);
        $args->set('subject', new sExportMock());

        $subscriber = $this->getSubscriberWithMocks();
        $subscriber->onFilterProductExport($args);

        $this->assertSame(9.95, $args->getReturn()[0]['price'], 'The price should have been modified to 9.95');
        $this->assertSame(14.95, $args->getReturn()[0]['pseudoprice'], 'The pseudo price should have been modified to 14.95');
        $this->assertTrue($args->getReturn()[0]['has_promotion'], 'The product should have the flag has_promotion after export');
    }

    public function test_onFilterProductExport_will_update_prices_with_customer_group()
    {
        $args = new \Enlight_Event_EventArgs();
        $args->setReturn(self::DUMMY_PRODUCTS);

        $sExport = new sExportMock();
        $sExport->setCustomerGroup([
            'groupkey' => 'EK',
        ]);

        $args->set('subject', $sExport);

        $subscriber = $this->getSubscriberWithMocks();
        $subscriber->onFilterProductExport($args);

        $this->assertSame(9.95, $args->getReturn()[0]['price'], 'The price should have been modified to 9.95');
        $this->assertSame(14.95, $args->getReturn()[0]['pseudoprice'], 'The pseudo price should have been modified to 14.95');
        $this->assertTrue($args->getReturn()[0]['has_promotion'], 'The product should have the flag has_promotion after export');
    }

    public function test_onFilterProductExport_with_a_customer_group_that_has_no_promotions()
    {
        $args = new \Enlight_Event_EventArgs();
        $args->setReturn(self::DUMMY_PRODUCTS);

        $sExport = new sExportMock();
        $sExport->setCustomerGroup([
            'groupkey' => 'H',
            'id' => 2,
        ]);
        $sExport->setSettings([
            'languageID' => 1,
            'customergroupID' => 2,
        ]);

        $args->set('subject', $sExport);

        $subscriber = $this->getSubscriberWithMocks();
        $subscriber->onFilterProductExport($args);

        $this->assertArraySubset(self::DUMMY_PRODUCTS, $args->getReturn());
    }

    /**
     * @return ProductExport
     */
    private function getSubscriber()
    {
        return new ProductExport(
            $this->getPromotionExportService(),
            Shopware()->Container()->get('shopware.plugin.cached_config_reader'),
            Shopware()->Container()->get('session'),
            'SwagPromotion'
        );
    }

    /**
     * @return ProductExport
     */
    private function getSubscriberWithMocks()
    {
        return new ProductExport(
            $this->getPromotionExportService([$this->getPromotion()]),
            new CachedConfigReaderMock(),
            Shopware()->Container()->get('session'),
            'SwagPromotion'
        );
    }

    /**
     * @param array $activePromotions
     *
     * @return PromotionExport
     */
    private function getPromotionExportService(array $activePromotions = [])
    {
        Shopware()->Container()->get('swag_promotion.repository')->set($activePromotions);

        $listProductService = new ListProductService(
            Shopware()->Container()->get('shopware_storefront.list_product_gateway'),
            Shopware()->Container()->get('shopware_storefront.graduated_prices_service'),
            Shopware()->Container()->get('shopware_storefront.cheapest_price_service'),
            Shopware()->Container()->get('shopware_storefront.price_calculation_service'),
            Shopware()->Container()->get('shopware_storefront.media_service'),
            Shopware()->Container()->get('shopware_storefront.marketing_service'),
            Shopware()->Container()->get('shopware_storefront.vote_service'),
            Shopware()->Container()->get('shopware_storefront.category_service'),
            Shopware()->Container()->get('config')
        );

        $listProductDecorator = new ListProductDecorator(
            'SwagPromotion',
            $listProductService,
            Shopware()->Container()->get('swag_promotion.promotion_product_highlighter'),
            Shopware()->Container()->get('shopware.plugin.cached_config_reader'),
            null
        );

        $this->setPriceDisplaying($listProductDecorator);

        return new PromotionExport(
            Shopware()->Container()->get('swag_promotion.repository'),
            Shopware()->Container()->get('swag_promotion.promotion_product_highlighter'),
            $listProductDecorator,
            Shopware()->Container()->get('shopware_storefront.context_service')
        );
    }

    /**
     * @param ListProductDecorator $listProductService
     */
    private function setPriceDisplaying(ListProductDecorator $listProductService)
    {
        $reflectionClass = new \ReflectionClass(ListProductDecorator::class);
        $property = $reflectionClass->getProperty('priceDisplaying');
        $property->setAccessible(true);
        $property->setValue($listProductService, 'pseudo');
    }

    /**
     * @return Promotion
     */
    private function getPromotion()
    {
        return PromotionFactory::create([
            'type' => 'product.absolute',
            'name' => 'PHPUnit',
            'number' => 'TEST1234',
            'amount' => 5,
            'active' => true,
            'applyRules' => ['and' => [
                'productCompareRule0.20067316602042906' => [
                    'detail::ordernumber',
                    '=',
                    'SW10003',
                ],
            ]],
        ]);
    }
}

class sExportMock
{
    /**
     * @var array
     */
    public $sSettings = [
        'languageID' => 1,
        'customergroupID' => 1,
    ];

    /**
     * @var array
     */
    public $sCustomergroup;

    /**
     * @param array $settings
     */
    public function setSettings(array $settings)
    {
        $this->sSettings = $settings;
    }

    /**
     * @param array $group
     */
    public function setCustomerGroup(array $group)
    {
        $this->sCustomergroup = $group;
    }
}

class CachedConfigReaderMock extends CachedConfigReader
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ConfigReader $reader = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getByPluginName($pluginName, Shop $shop = null)
    {
        return [
            'promotionPricesInProductExport' => true,
        ];
    }
}
