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

/**
 * @medium
 * @group integration
 */
class TaxTest extends TestCase
{
    public function testNetCalculationInBasket()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    []
                ),
            ]
        );

        // set to 0 to use net prices
        Shopware()->System()->sUSERGROUPDATA['tax'] = 0;
        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10035', 1);
        // set to 1 again, so other tests are not affected
        $basket = Shopware()->Modules()->Basket()->sGetBasket();
        Shopware()->System()->sUSERGROUPDATA['tax'] = 1;

        $this->assertTrue(
            $basket['AmountNumeric'] === $basket['AmountNetNumeric'],
            "Basket amount: {$basket['AmountNumeric']} and basket net amount: {$basket['AmountNetNumeric']} are not equal"
        );
    }
}
