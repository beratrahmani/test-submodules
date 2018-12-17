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

namespace SwagPromotion\Tests\Functional\Components\ProductExport;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ListProductService;
use SwagPromotion\Components\Listing\ListProductDecorator;
use SwagPromotion\Components\ProductExport\PromotionExport;
use SwagPromotion\Struct\Promotion;
use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\Helper\PromotionFactory;

class PromotionExportTest extends TestCase
{
    use DatabaseTestCaseTrait;

    const DUMMY_CONFIG = ['languageID' => 1, 'customergroupID' => 1];
    const DUMMY_PRODUCTS = [['ordernumber' => 'SW10003', 'price' => 14.95]];

    public function test_construct()
    {
        $instance = new PromotionExport(
            Shopware()->Container()->get('swag_promotion.repository'),
            Shopware()->Container()->get('swag_promotion.promotion_product_highlighter'),
            Shopware()->Container()->get('shopware_storefront.list_product_service'),
            Shopware()->Container()->get('shopware_storefront.context_service')
        );

        $this->assertInstanceOf(PromotionExport::class, $instance);
    }

    /**
     * @dataProvider test_handleExport_with_invalid_config_DataProvider
     *
     * @param array $config
     */
    public function test_handleExport_with_invalid_config(array $config)
    {
        $service = $this->getPromotionExportService();

        $result = $service->handleExport(self::DUMMY_PRODUCTS, $config);
        $this->assertCount(1, $result);
        $this->assertSame(14.95, $result[0]['price'], 'The price should not have been touched at all');
    }

    /**
     * @return array
     */
    public function test_handleExport_with_invalid_config_DataProvider()
    {
        return [
            [['customergroupID' => 1]],
            [['languageID' => 1]],
        ];
    }

    /**
     * @dataProvider handleExport_with_different_promotion_types_DataProvider
     *
     * @param Promotion[] $promotions
     */
    public function test_handleExport_with_different_promotion_types($promotions = [])
    {
        $service = $this->getPromotionExportService($promotions);

        $result = $service->handleExport(self::DUMMY_PRODUCTS, self::DUMMY_CONFIG);

        $this->assertCount(1, $result);
        $this->assertSame(14.95, $result[0]['price'], 'The price should not have been touched at all');
    }

    /**
     * @return array
     */
    public function handleExport_with_different_promotion_types_DataProvider()
    {
        return [
            [[new Promotion(['type' => 'basket.absolute', 'active' => true])]],
            [[new Promotion(['type' => 'basket.percentage', 'active' => true])]],
            [[new Promotion(['type' => 'product.buyxgetyfree', 'active' => true])]],
            [[new Promotion(['type' => 'product.freegoods', 'active' => true])]],
            [[]],
        ];
    }

    public function test_handleExport_will_update_price()
    {
        $service = $this->getPromotionExportService([$this->getPromotion()]);

        $result = $service->handleExport(self::DUMMY_PRODUCTS, self::DUMMY_CONFIG);

        $this->assertCount(1, $result);
        $this->assertSame(9.95, $result[0]['price'], 'The price should be updated to 9,95€');
        $this->assertSame(14.95, $result[0]['pseudoprice'], 'The pseudo price should be updated to 14,95€');
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

        $promotionExport = new PromotionExport(
            Shopware()->Container()->get('swag_promotion.repository'),
            Shopware()->Container()->get('swag_promotion.promotion_product_highlighter'),
            $listProductDecorator,
            Shopware()->Container()->get('shopware_storefront.context_service')
        );

        return $promotionExport;
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
