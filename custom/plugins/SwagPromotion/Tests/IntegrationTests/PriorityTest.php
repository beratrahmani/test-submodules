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
class PriorityTest extends TestCase
{
    public function testExclusiveItemsShouldBeExclusive()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'ex1',
                        'exclusive' => 1,
                        'priority' => 1,
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'ex2',
                        'exclusive' => 0,
                        'priority' => 2,
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'ex3',
                        'exclusive' => 0,
                        'priority' => 1,
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'ex4',
                        'exclusive' => 0,
                        'priority' => -1,
                    ]
                ),
            ]
        );

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 1);
        Shopware()->Modules()->Basket()->sAddArticle('SW10010', 1);
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        foreach ($basket['content'] as $lineItem) {
            if (in_array($lineItem['ordernumber'], ['ex2', 'ex3', 'ex4'])) {
                $this->fail('Unexpected exclusive item');
            }
        }
    }

    public function testPriority()
    {
        Shopware()->Container()->get('swag_promotion.repository')->set(
            [
                PromotionFactory::create(
                    [
                        'number' => 'ex1',
                        'priority' => 5,
                        'amount' => 1,
                    ]
                ),
                PromotionFactory::create(
                    [
                        'number' => 'ex2',
                        'priority' => 4,
                        'stopProcessing' => true,
                        'amount' => 1,
                    ]
                ),
                // the next promotion should not apply - the previous one stopped
                PromotionFactory::create(
                    [
                        'number' => 'ex3',
                        'priority' => 3,
                        'amount' => 1,
                    ]
                ),
            ]
        );

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Modules()->Basket()->sAddArticle('SW10009', 1);
        Shopware()->Modules()->Basket()->sAddArticle('SW10010', 1);
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        foreach ($basket['content'] as $lineItem) {
            if (in_array($lineItem['ordernumber'], ['ex3'])) {
                $this->fail('Unexpected priority item');
            }
        }
    }
}
