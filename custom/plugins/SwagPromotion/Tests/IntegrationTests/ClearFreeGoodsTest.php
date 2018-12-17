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

use SwagPromotion\Components\Services\FreeGoodsService;
use SwagPromotion\Models\Repository\DummyRepository;
use SwagPromotion\Tests\Helper\PromotionFactory;
use SwagPromotion\Tests\PromotionTestCase;

/**
 * @medium
 * @group integration
 */
class ClearFreeGoodsTest extends PromotionTestCase
{
    public function testClearFreeGoodsProduct()
    {
        $this->dispatch('');

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
                        'applyRules' => ['and' => ['productCompareRule' => ['ordernumber', '=', 'SW10008']]],
                    ]
                ),
            ]
        );

        $promotionId1 = $promotionRepo->getActivePromotions()[0]->id;

        $basketCore->sDeleteBasket();
        $basketCore->sAddArticle('SW10009', 1); // some product
        $basketCore->sAddArticle('SW10008', 1); // some product

        $basket = $basketCore->sGetBasket();

        $this->assertEquals(24.99 + 49.95, $basket['AmountNumeric'], '', 0.01);

        $freeGoodsService->addArticleAsFreeGood('SW10006', $promotionId1);

        // delete the product which grants the promotion
        $basketIdForDeleting = $basket['content'][1]['id'];
        $basketCore->sDeleteArticle($basketIdForDeleting);

        $basket = $basketCore->sGetBasket();

        $this->assertEquals(24.99 + 35.95, $basket['AmountNumeric'], '', 0.01);
    }
}
