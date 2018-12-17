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

namespace SwagPromotion\Components\Promotion\DiscountHandler\ProductHandler;

use SwagPromotion\Components\Promotion\DiscountCommand\Command\DiscountCommand;
use SwagPromotion\Components\Promotion\DiscountHandler\DiscountHandler;
use SwagPromotion\Struct\Promotion;

/**
 * BuyXGetYFreeProductHandler handles discounts of the type "buy x get y for free".
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class BuyXGetYFreeProductHandler implements DiscountHandler
{
    const BUY_X_GET_Y_FREE_PRODUCT_HANDLER_NAME = 'product.buyxgetyfree';

    /**
     * {@inheritdoc}
     */
    public function getDiscountCommand($basket, $stackedProducts, Promotion $promotion)
    {
        $discount = 0.0;
        $amount = $promotion->amount;

        foreach ($stackedProducts as $stack) {
            // sum up the prices of the free items
            $discount += array_sum(
            // return the price of the free items
                array_map(
                    function ($product) {
                        return $product['price'];
                    },
                    // get the "free" items
                    array_slice($stack, 0, $amount)
                )
            );
        }

        return new DiscountCommand($discount);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        return $name === self::BUY_X_GET_Y_FREE_PRODUCT_HANDLER_NAME;
    }
}
