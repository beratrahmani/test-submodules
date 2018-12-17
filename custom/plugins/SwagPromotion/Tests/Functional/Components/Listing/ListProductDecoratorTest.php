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

namespace SwagCustomProducts\Components\Services;

if (!interface_exists('\SwagCustomProducts\Components\Services\TemplateServiceInterface')) {
    interface TemplateServiceInterface
    {
    }
}

namespace SwagPromotion\Tests\Functional;

use ReflectionClass;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ListProductService;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Test\Plugin\TestCase;
use SwagCustomProducts\Components\Services\TemplateServiceInterface;
use SwagPromotion\Components\Listing\ListProductDecorator;
use SwagPromotion\Components\Listing\PromotionProductHighlighter;
use SwagPromotion\Struct\ListProduct\PromotionContainerStruct;
use SwagPromotion\Struct\Promotion;
use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\Helper\PromotionFactory;

/**
 * @small
 */
class ListProductDecoratorTest extends TestCase
{
    use DatabaseTestCaseTrait;

    /**
     * @var ListProductDecorator
     */
    private $listProductDecorator;

    public function testListProductServiceShouldBeDecorated()
    {
        $this->assertInstanceOf(
            ListProductDecorator::class,
            $this->getProductService()
        );
    }

    public function testPromotionAttributeShouldBeAvailableForPromotions()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );

        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertInstanceOf(
            PromotionContainerStruct::class,
            $result->getAttribute('promotion')
        );

        /** @var ListProduct[] $result */
        $result = $this->getProductService()->getList(['SW10009'], $this->getContext());
        // product without promotion does not get a promotion attribute
        $this->assertNull($result['SW10009']->getAttribute('promotion'));
    }

    public function testPriceShouldNotBeModifiedByDefault()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );

        $this->setConfigOnProductService('normal');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(35.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function testPriceConfigShouldOverwritePrice()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(25.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function testPseudoConfigShouldSetPseudoPrice()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('pseudo');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(25.95, $result->getPrices()[0]->getCalculatedPrice());
        $this->assertEquals(35.95, $result->getPrices()[0]->getCalculatedPseudoPrice());
    }

    public function testPercentageProductDiscount()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'percentage',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.percentage',
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(32.355, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function testPercentageDiscountCannotBeHigherThanProduct()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'percentage',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.percentage',
                        'amount' => 200,
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(35.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function testAbsoluteDiscountCannotBeHigherThanProduct()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'percentage',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 200,
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(35.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function testOneAbsoluteDiscountEqualsProductPrice()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'percentage',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 35.95,
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(0.01, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function testMultipleDiscountsSumUp()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute1',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'absolute2',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(15.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function testPromotionWithStepIsIgnored()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute1',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'step' => 2,
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'absolute2',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(25.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function testStopPromotion()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute2',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'priority' => 0,
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'absolute1',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'stopProcessing' => true,
                        'priority' => 1,
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price');
        $result = $this->getProductService()->get('SW10006', $this->getContext());
        $this->assertEquals(25.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function testExclusivePromotion()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute1',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10023']]],
                        'type' => 'product.absolute',
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'absolute2',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10023']]],
                        'type' => 'product.absolute',
                        'exclusive' => 1,
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price');
        $result = $this->getProductService()->get('SW10023', $this->getContext());
        $this->assertEquals(25, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function test_getList_there_should_be_no_shop()
    {
        $decoratedListProductService = $this->getProductService();
        $numbers = $this->getNumbers();
        $context = $this->getContext();

        $shop = Shopware()->Container()->get('shop');

        Shopware()->Container()->reset('shop');
        $result = $decoratedListProductService->getList($numbers, $context);
        Shopware()->Container()->set('shop', $shop);

        $this->assertCount(4, $result);
    }

    public function test_modifyPrices_product_price_is_calculated()
    {
        $this->setPromotionRepository();

        $decoratedListProductService = $this->getProductService();
        $context = $this->getContext();
        $product = $decoratedListProductService->get('SW10178', $context);
        $products = $decoratedListProductService->getList($this->getNumbers(), $context);

        $decoratorReflection = new ReflectionClass(ListProductDecorator::class);
        $method = $decoratorReflection->getMethod('modifyPrices');
        $method->setAccessible(true);

        $property = $decoratorReflection->getProperty('productHighlighter');
        $property->setAccessible(true);

        /** @var PromotionProductHighlighter $productHighlighter */
        $productHighlighter = $property->getValue($decoratedListProductService);

        $promotions = $productHighlighter->getProductPromotions($products, $context);

        $productReflection = new ReflectionClass(ListProduct::class);
        $property = $productReflection->getProperty('states');
        $property->setAccessible(true);
        $property->setValue($product, ['promotion_price_is_calculated' => true]);

        $invokeResult = $method->invoke($decoratedListProductService, $product, $promotions['SW10178']->promotions);
        /** @var Promotion $result */
        $result = array_shift($invokeResult);

        $this->assertSame('absolute1', $result->number);
    }

    public function test_price_config_should_overwrite_price_custom_products_service_active()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price', true);
        $result = $this->getProductService(true)->get('SW10006', $this->getContext());
        $this->assertEquals(25.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function test_price_config_should_overwrite_price_custom_products_template_inactive()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price', true);
        $result = $this->getProductService(true)->get('SW10006', $this->getContext());
        $this->assertEquals(25.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    public function test_price_config_should_not_overwrite_price_because_custom_products_template_is_active()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );
        $this->setConfigOnProductService('price', true, true);
        $result = $this->getProductService(true, true)->get('SW10006', $this->getContext());
        $this->assertEquals(35.95, $result->getPrices()[0]->getCalculatedPrice());
    }

    /**
     * @dataProvider calculateNewPriceTest_dataProvider
     *
     * @param array       $price
     * @param array       $promotion
     * @param ListProduct $product
     * @param array       $expected
     */
    public function test_calculateNewPrice(array $price, array $promotion, ListProduct $product, array $expected)
    {
        $decorator = $this->getProductService();

        $reflectionClass = new ReflectionClass(ListProductDecorator::class);
        $method = $reflectionClass->getMethod('calculateNewPrice');
        $method->setAccessible(true);

        $property = $reflectionClass->getProperty('priceDisplaying');
        $property->setAccessible(true);
        $property->setValue($decorator, 'pseudo');

        $result = $method->invokeArgs($decorator, [$price, $promotion, $product]);

        $this->assertSame(
            $expected['calculatedPrice'],
            $result['newPrices'][0]->getCalculatedPrice()
        );

        $this->assertSame(
            $expected['calculatedPseudoPrice'],
            $result['newPrices'][0]->getCalculatedPseudoPrice()
        );
    }

    /**
     * @return array
     */
    public function calculateNewPriceTest_dataProvider()
    {
        $product = new ListProduct(2, 123, 'SW10002.1');

        return [
            [
                $this->getPrice(12, 0.0, 12),
                $this->getPromotion('product.freegoods'),
                $product,
                [
                    'calculatedPrice' => 12,
                    'calculatedPseudoPrice' => 0.0,
                ],
            ],
            [
                $this->getPrice(12, 12, 12),
                $this->getPromotion('product.freegoods'),
                $product,
                [
                    'calculatedPrice' => 12,
                    'calculatedPseudoPrice' => 12,
                ],
            ],
            [
                $this->getPrice(12.0, 15.99, 12.0 * 1.16),
                $this->getPromotion('product.freegoods'),
                $product,
                [
                    'calculatedPrice' => 13.919999999999998,
                    'calculatedPseudoPrice' => 15.99,
                ],
            ],
            [
                $this->getPrice(12.0, 0.0, 12.0 * 1.16),
                $this->getPromotion('product.percentage'),
                $product,
                [
                    'calculatedPrice' => 12.527999999999999,
                    'calculatedPseudoPrice' => 13.919999999999998,
                ],
            ],
            [
                $this->getPrice(15.0, 0.0, 15.0 * 1.16),
                $this->getPromotion('product.percentage'),
                $product,
                [
                    'calculatedPrice' => 15.659999999999998,
                    'calculatedPseudoPrice' => 17.4,
                ],
            ],
            [
                $this->getPrice(15.0, 0.0, 15.0 * 1.16),
                $this->getPromotion('product.absolute'),
                $product,
                [
                    'calculatedPrice' => 7.399999999999999,
                    'calculatedPseudoPrice' => 17.4,
                ],
            ],
            [
                $this->getPrice(15.0, 0.0, 15.0 * 1.16),
                $this->getPromotion('product.absolute'),
                $product,
                [
                    'calculatedPrice' => 7.399999999999999,
                    'calculatedPseudoPrice' => 17.4,
                ],
            ],
        ];
    }

    /**
     * @return ShopContextInterface
     */
    protected function getContext()
    {
        return Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1);
    }

    /**
     * @param string $type
     *
     * @return array
     */
    private function getPromotion($type)
    {
        $promotion = include __DIR__ . '/Fixtures/promotion.php';
        $promotion->type = $type;

        return [
            $promotion,
        ];
    }

    /**
     * @param float $price
     * @param float $pseudoPrice
     * @param float $calculatedPrice
     *
     * @return array
     */
    private function getPrice($price, $pseudoPrice, $calculatedPrice)
    {
        $group = new Group();
        $group->setId(1);
        $group->setKey('EK');
        $group->setName('Shopkunden');
        $group->setDisplayGrossPrices(true);
        $group->setInsertedGrossPrices(true);
        $group->setUseDiscount(false);

        $unit = new Unit();
        $unit->setId(1);
        $unit->setName('Liter');
        $unit->setUnit('l');
        $unit->setPackUnit(1);
        $unit->setReferenceUnit(1.0);
        $unit->setPackUnit('Flaschen');
        $unit->setMinPurchase(1);

        $priceRule = new PriceRule();
        $priceRule->setId(148);
        $priceRule->setPrice($price);
        $priceRule->setFrom(1);
        $priceRule->setPseudoPrice($pseudoPrice);
        $priceRule->setCustomerGroup($group);
        $priceRule->setUnit($unit);

        $price = new Price($priceRule);
        $price->setCalculatedPrice($calculatedPrice);
        $price->setCalculatedPseudoPrice($pseudoPrice);

        return [
            $price,
        ];
    }

    private function setPromotionRepository()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute1',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10178']]],
                        'type' => 'product.absolute',
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'absolute1',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10179.1']]],
                        'type' => 'product.absolute',
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'absolute1',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10211']]],
                        'type' => 'product.absolute',
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'absolute1',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10229']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );
    }

    /**
     * @return array
     */
    private function getNumbers()
    {
        return [
            'SW10178',      // Strandtuch "Ibiza"
            'SW10179.1',    // Strandtuch in mehreren Farben
            'SW10211',      // Surfbrett
            'SW10229',      // Strandbag Sailor
        ];
    }

    /**
     * @param bool $addTemplateServiceMock
     * @param bool $isTemplateActive
     *
     * @return ListProductDecorator
     */
    private function getProductService($addTemplateServiceMock = false, $isTemplateActive = false)
    {
        if ($this->listProductDecorator !== null) {
            return $this->listProductDecorator;
        }

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

        $templateServiceMock = null;
        if ($addTemplateServiceMock) {
            $templateServiceMock = new TemplateServiceMock($isTemplateActive);
        }

        $this->listProductDecorator = new ListProductDecorator(
            'SwagPromotion',
            $listProductService,
            Shopware()->Container()->get('swag_promotion.promotion_product_highlighter'),
            Shopware()->Container()->get('shopware.plugin.cached_config_reader'),
            $templateServiceMock
        );

        return $this->listProductDecorator;
    }

    /**
     * @param string $value
     * @param bool   $addTemplateServiceMock
     * @param bool   $isTemplateActive
     *
     * @throws \ReflectionException
     */
    private function setConfigOnProductService($value, $addTemplateServiceMock = false, $isTemplateActive = false)
    {
        $service = $this->getProductService($addTemplateServiceMock, $isTemplateActive);
        $reflectionClass = new ReflectionClass($service);
        $property = $reflectionClass->getProperty('priceDisplaying');
        $property->setAccessible(true);
        $property->setValue($service, $value);
    }
}

class TemplateServiceMock implements TemplateServiceInterface
{
    /**
     * @var array
     */
    private $template;

    /**
     * @param bool $isTemplateActive
     */
    public function __construct($isTemplateActive = false)
    {
        if ($isTemplateActive) {
            $this->template = [
                'active' => $isTemplateActive,
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateByProductId($productId, $enrichTemplate = true, $productPrice = 0.00)
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function enrichValues(
        array $values,
        array $valuePrices,
        $productPrice = 0.00,
        $customerGroupId,
        $fallbackId,
        array $medias
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function enrichOptions(
        array $options,
        array $values,
        array $optionPrices,
        $productPrice = 0.00,
        $customerGroupId,
        $fallbackId
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function isInternalNameAssigned($internalName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsByTemplateId($templateId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getPrices($optionId = null, $valueId = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionById($id, $productPrice = 0.00, $basketCalculation = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getValueById($id, $productPrice = 0.00, $basketCalculation = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function enrich(array $data, $productPrice = 0.00, $basketCalculation = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function enrichTemplate(array $template, $templateId, $productPrice = 0.00)
    {
    }
}
