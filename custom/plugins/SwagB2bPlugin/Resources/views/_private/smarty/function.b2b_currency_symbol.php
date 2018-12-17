<?php declare(strict_types=1);

/**
 * Gets the currency symbol for the template
 *
 * @return string
 */
function smarty_function_b2b_currency_symbol()
{
    return Shopware()->Container()->get('b2b_shop.shop')->getCurrentCurrencySymbol();
}
