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

namespace Shopware\SwagPromotion\Tests;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Components\Cart\Struct\DiscountContext;
use Shopware\Components\Test\Plugin\TestCase;
use SwagPromotion\Components\Cart\BasketQueryHelperDecorator;

class BasketQueryHelperDecoratorTest extends TestCase
{
    public function test_getInsertDiscountAttributeQuery()
    {
        $decorator = $this->getDecorator();
        $discountContext = $this->getDiscountContext();

        $result = $decorator->getInsertDiscountAttributeQuery($discountContext);

        $expectedResult = 'INSERT INTO s_order_basket_attributes (basketID) VALUES(:basketId)';

        $this->assertInstanceOf(QueryBuilder::class, $result);
        $this->assertSame($expectedResult, $result->getSQL());
    }

    public function test_getInsertDiscountAttributeQuery_has_extended_query()
    {
        $decorator = $this->getDecorator();
        $discountContext = $this->getDiscountContext();

        $discountContext->addAttribute(
            BasketQueryHelperDecorator::ATTRIBUTE_COLUMN_PROMOTION_ID,
            new Attribute([
                'id' => 1,
            ])
        );

        $result = $decorator->getInsertDiscountAttributeQuery($discountContext);

        $expectedResult = 'INSERT INTO s_order_basket_attributes (basketID, swag_promotion_id) VALUES(:basketId, :swag_promotion_id)';

        $this->assertInstanceOf(QueryBuilder::class, $result);
        $this->assertSame($expectedResult, $result->getSQL());
    }

    /**
     * @return DiscountContext
     */
    private function getDiscountContext()
    {
        return new DiscountContext(
            'sessionId',
            1,
            -5,
            'test discount',
            '08154711',
            4,
            1,
            false
        );
    }

    /**
     * @return BasketQueryHelperDecorator
     */
    private function getDecorator()
    {
        return new BasketQueryHelperDecorator(
            Shopware()->Container()->get('shopware.cart.basket_query_helper')
        );
    }
}
