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

use SwagPromotion\Components\Promotion\CurrencyConverter;
use SwagPromotion\Components\Promotion\DiscountCommand\Command\DiscountCommand;
use SwagPromotion\Components\Promotion\DiscountHandler\DiscountHandler;
use SwagPromotion\Struct\Promotion;

/**
 * AbsoluteProductHandler handles absolute discounts on products.
 * Discounts will never be higher then the actual product's price.
 */
class AbsoluteProductHandler implements DiscountHandler
{
    const ABSOLUTE_PRODUCT_HANDLER_NAME = 'product.absolute';

    /**
     * @var CurrencyConverter
     */
    private $currencyConverter;

    /**
     * AbsoluteProductHandler constructor.
     *
     * @param CurrencyConverter $currencyHandler
     */
    public function __construct(CurrencyConverter $currencyHandler)
    {
        $this->currencyConverter = $currencyHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountCommand($basket, $stackedProducts, Promotion $promotion)
    {
        $amount = $this->currencyConverter->convert($promotion->amount);
        $discount = 0.0;

        // calculate the total discount
        // if a stack has a total less then the amount,
        // the granted discount amount will be $price - 0.01 ct
        // so that the product / stack costs 0.01 ct
        foreach ($stackedProducts as $stack) {
            $prices = (float) array_sum(array_column($stack, 'price'));
            if ($prices > $amount) {
                $discount += $amount;
            } elseif ($prices === $amount) {
                $discount += $prices - 0.01;
            }
        }

        return new DiscountCommand($discount);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        return $name === self::ABSOLUTE_PRODUCT_HANDLER_NAME;
    }
}
