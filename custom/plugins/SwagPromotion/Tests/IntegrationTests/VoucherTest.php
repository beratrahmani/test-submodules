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
use SwagPromotion\Tests\Helper\PromotionFactory;
use SwagPromotionTestKernel;

/**
 * @medium
 * @group integration
 */
class VoucherTest extends TestCase
{
    public function testPromotionAllowsVoucher()
    {
        SwagPromotionTestKernel::start();

        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'amount' => 3,
                    ]
                ),
            ]
        );

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 1);
        Shopware()->Modules()->Basket()->sAddArticle('SW10010', 1);
        // for consistency we need to make sure, that the basket is calculated, when an voucher is added
        Shopware()->Modules()->Basket()->sGetBasket();
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        // 30.94 would be the default price
        // 3€ is the discount of our promotion
        // 5€ is the discount of the absolute voucher
        $this->assertTrue(
            abs($basket['AmountNumeric'] - (30.94 - 3 - 5)) <= 0.01,
            "Unexpected basket amount: {$basket['AmountNumeric']}"
        );
    }

    public function testPromotionDisallowsVoucher()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'amount' => 3,
                        'disallowVouchers' => true,
                    ]
                ),
            ]
        );

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 1);
        Shopware()->Modules()->Basket()->sAddArticle('SW10010', 1);
        // for consistency we need to make sure, that the basket is calculated, when an voucher is added
        Shopware()->Modules()->Basket()->sGetBasket();
        Shopware()->Modules()->Basket()->sAddVoucher('absolut');
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        // 30.94 would be the default price
        // 3€ is the discount of our promotion
        $this->assertTrue(
            abs($basket['AmountNumeric'] - (30.94 - 3)) <= 0.01,
            "Unexpected basket amount: {$basket['AmountNumeric']}"
        );
    }
}
