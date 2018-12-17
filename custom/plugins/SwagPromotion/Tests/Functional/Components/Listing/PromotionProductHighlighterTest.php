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

use Exception;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Test\Plugin\TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use SwagPromotion\Components\Listing\PromotionProductHighlighter;
use SwagPromotion\Struct\ListProduct\PromotionContainerStruct;
use SwagPromotion\Tests\Helper\PromotionFactory;

/**
 * @small
 */
class PromotionProductHighlighterTest extends TestCase
{
    /** @var Helper */
    private $helper;

    public function testResultShouldBeEmptyForBasketPromotion()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'amount' => 3,
                        'type' => 'basket.absolute',
                    ]
                ),
            ]
        );

        $highlighter = $this->getProductHighlighter();
        $context = $this->getContext();
        $result = $highlighter->getProductPromotions(
            [$this->getHelper()->getListProduct('SW10009', $context)],
            $context
        );
        $this->assertEmpty($result);
    }

    public function testFreeGoodShouldBeHighlighted()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'applyRules' => ['and' => ['productCompareRule' => ['categories.id', '=', '11']]],
                        'number' => 'freeGoods1',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6], // 6 = cigar special
                    ]
                ),
            ]
        );

        $highlighter = $this->getProductHighlighter();
        $context = $this->getContext();
        $result = $highlighter->getProductPromotions(
            $this->getHelper()->getListProducts(['SW10023', 'SW10006'], $context),
            $context
        );
        $this->assertInstanceOf(
            PromotionContainerStruct::class,
            $result['SW10023']
        );
        $this->assertCount(1, $result);
    }

    public function testProductDiscountShouldBeHighlighted()
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

        $highlighter = $this->getProductHighlighter();
        $context = $this->getContext();
        $result = $highlighter->getProductPromotions(
            $this->getHelper()->getListProducts(['SW10006', 'SW10009'], $context),
            $context
        );

        $this->assertInstanceOf(
            PromotionContainerStruct::class,
            $result['SW10006']
        );
        $this->assertCount(1, $result);
    }

    public function testCustomerRulePromotionShouldBeSkipped()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'rules' => ['and' => ['customerCompareRule' => ['user::id', '=', 1]]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );

        $highlighter = $this->getProductHighlighter();
        $context = $this->getContext();
        $result = $highlighter->getProductPromotions(
            $this->getHelper()->getListProducts(['SW10006', 'SW10009'], $context),
            $context
        );

        $this->assertEmpty($result);
    }

    public function testCustomerRulePromotionShouldBeSkippedWithCustomerLoggedIn()
    {
        Shopware()->Session()->offsetSet('sUserId', 2);
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'rules' => ['and' => ['customerCompareRule' => ['user::id', '=', 1]]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );

        $highlighter = $this->getProductHighlighter();
        $context = $this->getContext();
        $result = $highlighter->getProductPromotions(
            $this->getHelper()->getListProducts(['SW10006', 'SW10009'], $context),
            $context
        );

        $this->assertEmpty($result);
        Shopware()->Session()->offsetUnset('sUserId');
    }

    public function testCustomerRulePromotionShouldBeApplied()
    {
        Shopware()->Session()->offsetSet('sUserId', 1);
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'rules' => ['and' => ['customerCompareRule' => ['user::id', '=', 1]]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );

        $highlighter = $this->getProductHighlighter();
        $context = $this->getContext();
        $result = $highlighter->getProductPromotions(
            $this->getHelper()->getListProducts(['SW10006', 'SW10009'], $context),
            $context
        );

        $this->assertInstanceOf(
            PromotionContainerStruct::class,
            $result['SW10006']
        );
        $this->assertCount(2, $result);
        Shopware()->Session()->offsetUnset('sUserId');
    }

    /**
     * @return Helper
     */
    private function getHelper()
    {
        if (!$this->helper) {
            $this->helper = new Helper();
        }

        return $this->helper;
    }

    /**
     * @throws Exception
     *
     * @return PromotionProductHighlighter
     */
    private function getProductHighlighter()
    {
        return Shopware()->Container()->get('swag_promotion.promotion_product_highlighter');
    }

    /**
     * @return ShopContextInterface
     */
    private function getContext()
    {
        return Shopware()->Container()->get('shopware_storefront.context_service')->getProductContext();
    }
}
