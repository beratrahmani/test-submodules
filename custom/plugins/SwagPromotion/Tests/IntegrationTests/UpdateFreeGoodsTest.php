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

use Shopware\Components\Test\Plugin\TestCase;
use SwagPromotion\Components\Services\FreeGoodsService;
use SwagPromotion\Models\Repository\DummyRepository;
use SwagPromotion\Tests\Helper\PromotionFactory;
use SwagPromotionTestKernel;

/**
 * @medium
 * @group integration
 */
class UpdateFreeGoodsTest extends TestCase
{
    public function testUpdateQuantityOfFreeGoodsProduct()
    {
        SwagPromotionTestKernel::start();

        /** @var FreeGoodsService $freeGoodsService */
        $freeGoodsService = Shopware()->Container()->get('swag_promotion.service.free_goods_service');
        $basketCore = Shopware()->Modules()->Basket();
        /** @var DummyRepository $promotionRepo */
        $promotionRepo = Shopware()->Container()->get('swag_promotion.repository');
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
                        'number' => 'freeGoods2',
                        'type' => 'product.freegoods',
                        'freeGoods' => [6], // 6 = cigar special
                    ]
                ),
            ]
        );

        $promotions = $promotionRepo->getActivePromotions();

        foreach ($promotions as $promotion) {
            if ($promotion->number === 'freeGoods1') {
                $promotionId1 = $promotion->id;
            } elseif ($promotion->number === 'freeGoods2') {
                $promotionId2 = $promotion->id;
            }
        }

        $basketCore->sDeleteBasket();
        $basketCore->sAddArticle('SW10009', 1); // some product

        $freeGoodsService->addArticleAsFreeGood('SW10006', $promotionId1);
        $freeGoodsService->addArticleAsFreeGood('SW10006', $promotionId2);

        $basket = $basketCore->sGetBasket();

        $this->assertEquals(24.99, $basket['AmountNumeric'], '', 0.01);

        $basketIdForUpdate = $basket['content'][1]['id'];

        $basketCore->sUpdateArticle($basketIdForUpdate, 1);

        $basket = $basketCore->sGetBasket();

        $this->assertEquals(24.99, $basket['AmountNumeric'], '', 0.01);
    }
}
