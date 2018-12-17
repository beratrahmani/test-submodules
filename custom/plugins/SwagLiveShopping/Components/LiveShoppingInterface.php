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

namespace SwagLiveShopping\Components;

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use SwagLiveShopping\Models\LiveShopping as LiveShoppingModel;

/**
 * Used for all LiveShopping resource specified processes.
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
interface LiveShoppingInterface
{
    /**
     * Constant for the live shopping type "standard"
     *
     * The live shopping product contains a fix price definition. The product is selled for
     * the fix defined price while the live shopping product is active and in the date range.
     */
    const NORMAL_TYPE = 1;

    /**
     * Constant for the live shopping type "discount per minute"
     *
     * The live shopping product contains a definition for the start and end price.
     * Based on the valid from and valid to date, the product price will be descrease per minute.
     */
    const DISCOUNT_TYPE = 2;

    /**
     * Constant for the live shopping type "surcharge per minute"
     *
     * The live shopping product contains a definition for the start and end price.
     * Based on the valid from and valid to date, the product price will be increase per minute.
     */
    const SURCHARGE_TYPE = 3;

    /**
     * Returns all active live shopping products
     *
     * This function is used for the frontend listing and product detail page.
     * The function returns all defined live shopping products for the passed product id.
     * The returned live shopping definitions contains already the current price for the
     * current frontend session.
     *
     * @param int         $productId
     * @param Detail|null $variant
     *
     * @return LiveShoppingModel|false
     */
    public function getActiveLiveShoppingForProduct($productId, Detail $variant = null);

    /**
     * Returns the active live shopping action for the passed product variant.
     *
     * @param Detail $variant
     *
     * @return LiveShoppingModel|false
     */
    public function getActiveLiveShoppingForVariant(Detail $variant);

    /**
     * Global interface to validate a single live shopping product.
     *
     * This function is used to validate live shopping products for
     * the shopware basket.
     *
     * @param LiveShoppingModel $liveShopping
     *
     * @return array|bool
     */
    public function validateLiveShopping($liveShopping);

    /**
     * Gets the Reference Unit Price
     *
     * @param LiveShoppingModel $liveShopping
     * @param Detail|null       $product
     *
     * @return float
     */
    public function getReferenceUnitPriceForLiveShopping(LiveShoppingModel $liveShopping, $product = null);

    /**
     * Returns a single active live shopping product.
     *
     * This function is used to refresh the live shopping data on the product
     * detail page if one minute remained.
     *
     * @param int $liveShoppingId
     *
     * @return bool|LiveShoppingModel
     */
    public function getActiveLiveShoppingById($liveShoppingId);

    /**
     * Array mapping function.
     *
     * This function is used to convert the passed LiveShoppingModel model into array data.
     *
     * @param LiveShoppingModel $liveShopping
     *
     * @return array|bool
     */
    public function getLiveShoppingArrayData($liveShopping);

    /**
     * Helper function to get the current tax rate.
     *
     * @param Article $product
     *
     * @return float
     */
    public function getCurrentTaxRate(Article $product);

    /**
     * Helper function to get the current currency factor for the store front.
     *
     * @return int
     */
    public function getCurrentCurrencyFactor();

    /**
     * Helper function to check if the current customer should see net prices for products.
     *
     * @return bool
     */
    public function displayNetPrices();

    /**
     * Helper function to check if the shopware basket should use gross or net prices
     * for the current logged in customer.
     *
     * @deprecated Will be removed in 4.0.0. Use function displayNetPrices() instead.
     *
     * @return bool
     */
    public function useNetPriceInBasket();

    /**
     * @param LiveShoppingModel $liveShopping
     *
     * @return string
     */
    public function getLiveShoppingProductName($liveShopping);

    /**
     * @param LiveShoppingModel $liveShopping
     *
     * @return bool
     */
    public function isLiveShoppingDateActive($liveShopping);

    /**
     * Returns all basket live shoppings.
     *
     * This function is used to get all live shopping products
     * which placed in the shopware basket for the current
     * frontend session.
     *
     * @return array
     */
    public function getBasketLiveShoppingProducts();

    /**
     * Returns live shopping product.
     * If we have limited variants - returns first variant, otherwise - returns main product.
     *
     * @param LiveShoppingModel $liveShopping
     *
     * @return Detail
     */
    public function getProductByLiveShopping(LiveShoppingModel $liveShopping);

    /**
     * Helper function to decrease the stock value of the passed live shopping product.
     *
     * @param LiveShoppingModel $liveShopping
     * @param int               $quantity
     */
    public function decreaseLiveShoppingStock(LiveShoppingModel $liveShopping, $quantity = 1);

    /**
     * Helper function to check if the passed product variant is allowed for the passed live shopping product.
     *
     * @param LiveShoppingModel $liveShopping
     * @param Detail            $variant
     *
     * @return bool
     */
    public function isVariantAllowed(LiveShoppingModel $liveShopping, Detail $variant);

    /**
     * Return live shopping data for product by product number
     *
     * @param string $number
     *
     * @return array|false
     */
    public function getLiveShoppingByNumber($number);

    /**
     * @param ListProduct[]        $products
     * @param ShopContextInterface $context
     *
     * @return string[]
     */
    public function haveVariantsLiveShopping(array $products, ShopContextInterface $context);
}
