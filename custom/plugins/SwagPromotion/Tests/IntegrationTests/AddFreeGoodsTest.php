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

namespace SwagPromotion\Tests\IntegrationTests;

use Enlight_Components_Session_Namespace;
use Enlight_Exception;
use Exception;
use Shopware\Components\DependencyInjection\Container;
use SwagPromotion\Components\Services\FreeGoodsService;
use SwagPromotion\Models\Repository\DummyRepository;
use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\Helper\PromotionFactory;
use SwagPromotion\Tests\PromotionTestCase;
use SwagPromotionTestKernel;

/**
 * @medium
 * @group integration
 */
class AddFreeGoodsTest extends PromotionTestCase
{
    use DatabaseTestCaseTrait;
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        SwagPromotionTestKernel::start();

        parent::setUp();

        $this->container = Shopware()->Container();
        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestHttp());
    }

    /**
     * This is an implementation detail, that should make the tests fail, if changed
     *
     * @throws Enlight_Exception
     * @throws Exception
     */
    public function testAddingCigarSpecialAsNormalProductShouldNotAddDiscount()
    {
        $this->container->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'freeGoods1',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6], // 6 = cigar special
                    ]
                ),
            ]
        );

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 1);
        Shopware()->Modules()->Basket()->sAddArticle('SW10006', 1);
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        foreach ($basket['content'] as $lineItem) {
            if ($lineItem['ordernumber'] === 'freeGoods1') {
                $this->fail('There should be no discount on cigar special!');
            }
        }

        $this->assertTrue(abs(60.94 - $basket['AmountNumeric']) < 0.01);
    }

    /**
     * Once a free good is added explicitly, we should get the discount
     *
     * @throws Enlight_Exception
     * @throws Exception
     */
    public function testAddingCigarSpecialExplicitlyShouldGrantADiscount()
    {
        /** @var DummyRepository $promotionRepo */
        $promotionRepo = $this->container->get('swag_promotion.repository');
        $promotionRepo->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'freeGoods1',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6], // 6 = cigar special
                    ]
                ),
            ]
        );

        $promotionId = $promotionRepo->getActivePromotions()[0]->id;

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 1);

        // now adding as a freeGood explicitly
        $this->container->get('swag_promotion.service.free_goods_service')
            ->addArticleAsFreeGood('SW10006', $promotionId);

        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $this->assertEquals(24.99, $basket['AmountNumeric'], '', 0.01);

        $found = false;
        foreach ($basket['content'] as $lineItem) {
            if ($lineItem['ordernumber'] === 'freeGoods1') {
                $found = true;
            }
        }
        if (!$found) {
            $this->fail('freeGood promotion was not added');
        }
    }

    /**
     * Percental basket discount should not give discount on free goods
     *
     * @throws Enlight_Exception
     * @throws Exception
     */
    public function testPercentageBasketDiscountShouldIgnoreFreeGoods()
    {
        /** @var DummyRepository $promotionRepo */
        $promotionRepo = $this->container->get('swag_promotion.repository');
        $promotionRepo->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'freeGoods1',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6], // 6 = cigar special
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'percentageBasket',
                        'type' => 'basket.percentage',
                        'amount' => 10,
                    ]
                ),
            ]
        );

        $promotions = $promotionRepo->getActivePromotions();

        foreach ($promotions as $promotion) {
            if ($promotion->number === 'freeGoods1') {
                $promotionId = $promotion->id;
            }
        }

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 2);
        // now adding as a freeGood explicitly
        $this->container->get('swag_promotion.service.free_goods_service')->addArticleAsFreeGood('SW10006', $promotionId);
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $usualTotal = 24.99 * 2 + 35.95;
        $discount = 35.95 + (24.99 * 2 * 0.1);

        $this->assertEquals($usualTotal - $discount, $basket['AmountNumeric'], '', 0.01);
    }

    /**
     * Percentage article discount should not give discount on free goods
     *
     * @throws Enlight_Exception
     * @throws Exception
     */
    public function testPercentageArticleDiscountShouldIgnoreFreeGoods()
    {
        /** @var DummyRepository $promotionRepo */
        $promotionRepo = $this->container->get('swag_promotion.repository');
        $promotionRepo->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'freeGoods1',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6], // 6 = cigar special
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'percentageProduct',
                        'type' => 'product.percentage',
                        'amount' => 10,
                    ]
                ),
            ]
        );

        $promotions = $promotionRepo->getActivePromotions();

        foreach ($promotions as $promotion) {
            if ($promotion->number === 'freeGoods1') {
                $promotionId = $promotion->id;
            }
        }

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 2);
        // now adding as a freeGood explicitly
        $this->container->get('swag_promotion.service.free_goods_service')->addArticleAsFreeGood('SW10006', $promotionId);
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $usualTotal = 24.99 * 2 + 35.95;
        $discount = 35.95 + (24.99 * 2 * 0.1);

        $this->assertEquals($usualTotal - $discount, $basket['AmountNumeric'], '', 0.01);
    }

    /**
     * Absolute article discount should not give discount on free goods
     *
     * @throws Enlight_Exception
     * @throws Exception
     */
    public function testAbsoluteArticleDiscountShouldIgnoreFreeGoods()
    {
        /** @var DummyRepository $promotionRepo */
        $promotionRepo = $this->container->get('swag_promotion.repository');
        $promotionRepo->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'freeGoods1',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6], // 6 = cigar special
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'absoluteProduct',
                        'type' => 'product.absolute',
                        'amount' => 1,
                    ]
                ),
            ]
        );

        $promotions = $promotionRepo->getActivePromotions();

        foreach ($promotions as $promotion) {
            if ($promotion->number === 'freeGoods1') {
                $promotionId = $promotion->id;
            }
        }

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 2);
        // now adding as a freeGood explicitly
        $this->container->get('swag_promotion.service.free_goods_service')->addArticleAsFreeGood('SW10006', $promotionId);
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $usualTotal = 24.99 * 2 + 35.95;
        $discount = 35.95 + 2;

        $this->assertEquals($usualTotal - $discount, $basket['AmountNumeric'], '', 0.01);
    }

    /**
     * @throws Enlight_Exception
     * @throws Exception
     */
    public function testMultipleFreeGoodsShouldGiveDiscountForOnlyOne()
    {
        /** @var DummyRepository $promotionRepo */
        $promotionRepo = $this->container->get('swag_promotion.repository');
        $promotionRepo->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'freeGoods1',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6], // 6 = cigar special
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'percentageBasket',
                        'type' => 'basket.percentage',
                        'amount' => 10,
                    ]
                ),
            ]
        );

        $promotions = $promotionRepo->getActivePromotions();

        foreach ($promotions as $promotion) {
            if ($promotion->number === 'freeGoods1') {
                $promotionId = $promotion->id;
            }
        }

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 2);
        Shopware()->Modules()->Basket()->sAddArticle('SW10006', 1);
        // now adding as a freeGood explicitly
        $this->container->get('swag_promotion.service.free_goods_service')->addArticleAsFreeGood('SW10006', $promotionId);
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        $usualTotal = round(24.99 * 2, 2) + round(35.95 * 2, 2);
        $discount = 35.95 + round(24.99 * 2 * 0.1 + 35.95 * 0.1, 2);

        $this->assertEquals($usualTotal - $discount, $basket['AmountNumeric'], '', 0.01);
    }

    public function testManyFreeGoodsPromotions()
    {
        /** @var DummyRepository $promotionRepo */
        $basketModule = Shopware()->Modules()->Basket();

        /** @var FreeGoodsService $freeGoodsService */
        $freeGoodsService = $this->container->get('swag_promotion.service.free_goods_service');

        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->container->get('session');
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sessionId', 'sessionId');
        $session->offsetSet('sUserPassword', 'a256a310bc1e5db755fd392c524028a8');
        $session->offsetSet('sUserMail', 'test@example.com');

        $basketModule->sDeleteBasket();

        $sql = file_get_contents(__DIR__ . '/Fixtures/addFreeGoodsPromotions.sql');
        $this->container->get('dbal_connection')->exec($sql);
        $basketModule->sRefreshBasket();

        $res = 80.86;
        $basket = $basketModule->sGetBasket();

        // basket amount must be still the same
        $this->assertEquals(
            $res,
            $basket['AmountNumeric'],
            '',
            0.01
        );
    }
}
