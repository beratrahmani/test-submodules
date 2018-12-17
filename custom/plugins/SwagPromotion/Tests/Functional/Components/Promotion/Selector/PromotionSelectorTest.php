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

namespace SwagPromotion\Tests\Functional\Components\Promotion\Selector;

use SwagPromotion\Components\Promotion\Selector\PromotionSelector;
use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\Helper\PromotionFactory;

class PromotionSelectorTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseTestCaseTrait;

    public function test_PromotionSelector_could_be_created()
    {
        $this->assertInstanceOf(PromotionSelector::class, $this->getPromotionSelector());
    }

    public function test_apply_simple_promotion()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertSame(9999, $appliedPromotions->promotionIds[0]);
    }

    public function test_apply_stop_processing()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9998,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'stopProcessing' => true,
                        'amount' => 1,
                    ]
                ),

                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 1,
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertCount(1, $appliedPromotions->promotionIds);
        $this->assertSame(9998, $appliedPromotions->promotionIds[0]);
    }

    public function test_apply_do_not_run_after()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9998,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 1,
                    ]
                ),

                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 1,
                        'doNotRunAfter' => [9998],
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertCount(1, $appliedPromotions->promotionIds);
        $this->assertSame(9998, $appliedPromotions->promotionIds[0]);
    }

    public function test_apply_do_not_allow_later()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9998,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 1,
                        'doNotAllowLater' => [9999],
                    ]
                ),

                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 1,
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertCount(1, $appliedPromotions->promotionIds);
        $this->assertSame(9998, $appliedPromotions->promotionIds[0]);
    }

    public function test_apply_exclusive_promotion()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9998,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 1,
                    ]
                ),

                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 1,
                        'exclusive' => 1,
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertCount(1, $appliedPromotions->promotionIds);
        $this->assertSame(9999, $appliedPromotions->promotionIds[0]);
    }

    public function test_apply_promotions_does_not_match()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10007']]],
                        'type' => 'product.absolute',
                        'amount' => 1,
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertNull($appliedPromotions->promotionIds);
    }

    public function test_apply_promotions_used_too_often()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'amount' => 1,
                        'maxUsage' => 1,
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));
        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/customer_count.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertNull($appliedPromotions->promotionIds);
    }

    public function test_apply_free_goods_promotion_excluding_each_other()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9997,
                        'number' => 'freeGoods1',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6],
                        'doNotAllowLater' => [9998, 9999],
                    ]
                ),
                PromotionFactory::create(
                    [
                        'id' => 9998,
                        'number' => 'freeGoods2',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6],
                        'doNotAllowLater' => [9997, 9999],
                    ]
                ),
                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'freeGoods3',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6],
                        'doNotAllowLater' => [9997, 9998],
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertEmpty($appliedPromotions->freeGoodsArticlesIds);
        $this->assertCount(1, $appliedPromotions->promotionIds);
    }

    public function test_apply_free_goods_promotion_does_not_match()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9997,
                        'number' => 'freeGoods1',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6],
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10008']]],
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertNull($appliedPromotions->freeGoodsArticlesIds);
        $this->assertNull($appliedPromotions->promotionIds);
    }

    public function test_apply_invalid_basket_rule()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'freeGoods3',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6],
                        'rules' => ['and' => ['basketCompareRule' => ['amountGross', '>=', 200]]],
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertNull($appliedPromotions->freeGoodsArticlesIds);
        $this->assertNull($appliedPromotions->promotionIds);
    }

    public function test_apply_promotions_with_apply_rules_first()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'rules' => ['and' => ['basketCompareRule' => ['amountGross', '>=', 70]]],
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'applyRulesFirst' => true,
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket2.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertCount(1, $appliedPromotions->promotionIds);
        $this->assertSame(9999, $appliedPromotions->promotionIds[0]);
    }

    public function test_apply_promotions_with_apply_rules_first_fail()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'rules' => ['and' => ['basketCompareRule' => ['amountGross', '>=', 80]]],
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'applyRulesFirst' => true,
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket2.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertNull($appliedPromotions->promotionIds);
        $this->assertCount(1, $appliedPromotions->promotionsDoNotMatch);
    }

    public function test_apply_promotions_with_apply_rules_first_no_matches()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'rules' => ['and' => ['basketCompareRule' => ['amountGross', '>=', 80]]],
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'applyRulesFirst' => true,
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket3.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertNull($appliedPromotions->promotionIds);
        $this->assertNull($appliedPromotions->promotionsDoNotMatch);
    }

    public function test_apply_promotions_with_apply_rules_first_shipping_free_products()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'id' => 9999,
                        'number' => 'absolute',
                        'rules' => ['and' => ['basketCompareRule' => ['amountGross', '>=', 70]]],
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10006']]],
                        'type' => 'product.absolute',
                        'applyRulesFirst' => true,
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        Shopware()->Session()->offsetSet('sessionId', 'test-session');

        $this->execSql(file_get_contents(__DIR__ . '/_fixtures/basket4.sql'));

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $appliedPromotions = $selector->apply($basket, 1, 1, 1);

        $this->assertCount(1, $appliedPromotions->promotionIds);
        $this->assertSame(9999, $appliedPromotions->promotionIds[0]);
    }

    public function test_do_not_apply_promotion_becaus_of_empty_basket()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'absolute',
                        'rules' => ['and' => ['basketCompareRule' => ['amountGross', '>=', 70]]],
                        'type' => 'product.absolute',
                    ]
                ),
            ]
        );

        $selector = $this->getPromotionSelector();

        $appliedPromotions = $selector->apply([], 1, 1, 1);

        $this->assertNull($appliedPromotions->promotionIds);
        $this->assertNull($appliedPromotions->promotionsDoNotMatch);
    }

    /**
     * @return PromotionSelector
     */
    private function getPromotionSelector()
    {
        return Shopware()->Container()->get('swag_promotion.promotion_selector');
    }
}
