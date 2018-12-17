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

namespace SwagAboCommerce\Services;

use Doctrine\ORM\AbstractQuery;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price;
use Shopware\Models\Customer\Group;
use Shopware\Models\Order\Basket;

interface AboCommerceBasketServiceInterface
{
    /**
     * Constant for the exception case that
     * no valid order number passed to the add article function
     */
    const FAILURE_NO_VALID_ORDER_NUMBER = 1;

    /**
     * Constant for the exception case that
     * the current session identified as bot session.
     */
    const FAILURE_BOT_SESSION = 2;

    /**
     * Constant for the exception case that
     * the notify until event prevent the process.
     */
    const FAILURE_ADD_PRODUCT_START_EVENT = 3;

    /**
     * Constant for the exception case that
     * one of the product has not enough stock.
     */
    const FAILURE_NOT_ENOUGH_STOCK = 4;

    /**
     * Global interface to add a single product to the basket.
     * The passed order number used as identifier for the product.
     * The passed quantity is used to identify how many times the customer want to
     * buy the product.
     *
     * <pre>
     * To add an product to the shopware basket, shopware checks the following conditions:
     * 1. The passed order number has to be a valid order number which defined over the s_articles_details table.
     * 2. The product and the variant of the passed order number has to been activated
     *    (active column in the s_articles and s_articles_details).
     * 3. The current customer group must be enabled for the selected product.
     * 4. The Shopware_Modules_Basket_AddArticle_Start notifyUntil event should not return TRUE
     * 5. The variant stock has to be greater or equal than the sum of the quantity of the basket
     *     for the current customer session  and the passed quantity for the passed product.
     * 6. The product must have defined a price.
     * </pre>
     *
     * @param string $orderNumber the order number of the product variant
     * @param int    $quantity    how many unit of the variant has to been added
     * @param array  $parameter   An optional array of process parameters which can be handled from plugins.
     *                            The Shopware standard process don't considers this property.
     *
     * @return array result of the add product to basket process
     */
    public function addProduct($orderNumber, $quantity = 1, array $parameter = []);

    /**
     * Returns the basket data for the passed basket row id.
     * The result set data type can be handled over the hydration mode parameter.
     * The hydration mode default is set to \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY,
     * you can pass \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT to get an instance of
     * \Shopware\Models\Order\Basket
     *
     * @param int $id
     * @param int $hydrationMode
     *
     * @return array|null
     */
    public function getItem($id, $hydrationMode = AbstractQuery::HYDRATE_ARRAY);

    /**
     * @param Basket $item
     */
    public function removeItem($item);

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the price column
     * of the s_order_basket.
     * Override this function to control an own price handling
     * in the basket section.
     *
     * @param Detail $variant
     *
     * @return array
     */
    public function getVariantPrices($variant);

    /**
     * Helper function for the getVariantData function.
     * Used to check the current shop session if the customer price
     * will be displayed as gross or net prices.
     * Override this function to control an own net and gross price handling
     * in the basket section.
     *
     * @param Price[] $prices
     * @param Detail  $variant
     *
     * @return array
     */
    public function getNetAndGrossPriceForVariantPrice(array $prices, $variant);

    /**
     * Helper function to get the current customer group of the logged in customer.
     * If the customer isn't logged in now, the function returns the default customer
     * group of the current sub shop.
     *
     * @return Group
     */
    public function getCurrentCustomerGroup();

    /**
     * @param int       $orderDetailId
     * @param string    $productOrderNumber
     * @param string    $modus
     *
     * @return int|null
     */
    public function addProductOnRecurringOrder($orderDetailId, $productOrderNumber, $modus);

    /**
     * Helper function to check if the current customer should see net prices for product.
     *
     * @return bool
     */
    public function displayNetPrices();

    /**
     * Helper function to check if the shopware basket should use gross or net prices
     * for the current logged in customer.
     *
     * @return bool
     */
    public function useNetPriceInBasket();
}
