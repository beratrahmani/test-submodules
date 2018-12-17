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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price;
use Shopware\Models\Customer\Group;
use Shopware\Models\Order\Basket;

/**
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
interface LiveShoppingBasketInterface
{
    /**
     * Constant for the exception case that
     * no valid order number passed to the add product function
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
     * one of the products has not enough stock.
     */
    const FAILURE_NOT_ENOUGH_STOCK = 4;

    /**
     * Getter function of the newBasketItem property.
     * This property is only used for php unit tests.
     *
     * @return Basket
     */
    public function getNewBasketItem();

    /**
     * Global interface to add a single product to the basket.
     * The passed order number used as identifier for the product.
     * The passed quantity is used to identify how many times the customer want to buy the product.
     *
     * <pre>
     * To add a product to the shopware basket, shopware checks the following conditions:
     * 1. The passed order number has to be a valid order number which defined over the s_articles_details table.
     * 2. The product and the variant of the passed order number has to been activated (active column in the s_articles and s_articles_details).
     * 3. The current customer group must be enabled for the selected product.
     * 4. The Shopware_Modules_Basket_AddArticle_Start notifyUntil event should not return TRUE
     * 5. The variant stock has to be greater or equal than the sum of the quantity of the basket for the current customer session
     * and the passed quantity for the passed product.
     * 6. The product must have defined a price.
     * </pre>
     *
     * @param string $orderNumber the order number of the product variant
     * @param int    $quantity    how many unit of the variant has to been added
     * @param array  $parameter   An optional array of process parameters which can be handled from plugins.
     *                            The Shopware standard process don't considers this property.
     *
     * @return array
     */
    public function addProduct($orderNumber, $quantity = 1, array $parameter = []);

    /**
     * Helper function to get the variant data for the new
     * basket position. To add additional information you can hook
     * this function and change the return value by an after hook.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return array
     */
    public function getVariantCreateData(Detail $variant, $quantity, array $parameter = []);

    /**
     * Helper function of the addProduct function.
     * Generates the default shopware attribute data,
     * based on the passed variant, for the new basket row.
     * To add additional basket attributes, use an Enlight_Hook_After
     * event to modify the return value.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return array
     */
    public function getAttributeCreateData(Detail $variant, $quantity, array $parameter = []);

    /**
     * Helper function to get the variant data for the updated
     * basket position. To add additional information you can hook
     * this function and change the return value by an after hook.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return array
     */
    public function getVariantUpdateData($variant, $quantity, array $parameter = []);

    /**
     * Helper function of the addProduct function.
     * Generates the default shopware attribute data,
     * based on the passed variant, for the updated basket row.
     * To add additional basket attributes, use an Enlight_Hook_After
     * event to modify the return value.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return array
     */
    public function getAttributeUpdateData($variant, $quantity, array $parameter = []);

    /**
     * Helper function to update an existing basket item.
     * The function expects an array with basket data.
     * All parameters of the addProduct function are also available here.
     *
     * @param int    $id
     * @param array  $data
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     */
    public function updateItem($id, array $data, Detail $variant, $quantity, array $parameter);

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
     * @return Basket|array|null
     */
    public function getItem($id, $hydrationMode = AbstractQuery::HYDRATE_ARRAY);

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the userID column
     * of the s_order_basket.
     *
     * @return null|int
     */
    public function getUserId();

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the articleName column
     * of the s_order_basket.
     * Override this function to control an own product name
     * handling in the basket section.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return string
     */
    public function getVariantName($variant, $quantity, array $parameter = []);

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the articleID column
     * of the s_order_basket.
     * Override this function to control an own product id handling of the
     * basket section.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return string
     */
    public function getProductId($variant, $quantity, array $parameter = []);

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the order number column
     * of the s_order_basket.
     * Override this function to control an own order number handling
     * in the basket section.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return string
     */
    public function getNumber($variant, $quantity, array $parameter = []);

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the shipping free column
     * of the s_order_basket.
     * Override this function to control an own shipping free handling
     * in the basket section.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return string
     */
    public function getShippingFree($variant, $quantity, array $parameter = []);

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the price column
     * of the s_order_basket.
     * Override this function to control an own price handling
     * in the basket section.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return array|false
     */
    public function getVariantPrice($variant, $quantity, array $parameter = []);

    /**
     * Helper function for the getVariantData function.
     * Used to check the current shop session if the customer price
     * will be displayed as gross or net prices.
     * Override this function to control an own net and gross price handling
     * in the basket section.
     *
     * @param Price  $price
     * @param Detail $variant
     * @param array  $parameter
     *
     * @return array
     */
    public function getNetAndGrossPriceForVariantPrice($price, $variant, array $parameter);

    /**
     * Helper function to select all prices for the passed variant and the passed
     * customer group. If the result set of the query is empty, the function
     * resume the query with the passed fallback customer group key.
     * To control the result data type you can use the $hydrationMode parameter.
     * The default is set to "\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY".
     * Set the parameter to "\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT" to get the result
     * set as \Shopware\Models\Article\Price instances.
     *
     * @param Detail $variant
     * @param string $customerGroupKey Contains the group key for the customer group
     * @param string $fallbackKey      Contains an fallback group key for the customer group
     * @param int    $hydrationMode    the hydration mode parameter control the result data type
     * @param array  $parameter
     *
     * @return array
     */
    public function getPricesForCustomerGroup(
        Detail $variant,
        $customerGroupKey,
        $fallbackKey,
        $hydrationMode = AbstractQuery::HYDRATE_ARRAY,
        array $parameter = []
    );

    /**
     * Helper function to get an query builder object which creates an select
     * on the product price table with a product detail id and customer group key
     * condition.
     * The result will be sorted by the from value of the prices.
     *
     * @return QueryBuilder
     */
    public function getPriceQueryBuilder();

    /**
     * Helper function to get the current customer group of the logged in customer.
     * If the customer isn't logged in now, the function returns the default customer
     * group of the current sub shop.
     *
     * @return Group
     */
    public function getCurrentCustomerGroup();

    /**
     * Helper function for the getVariantData function.
     * Used to get the stack price of the passed quantity.
     * The passed prices are already filtered by the customer group.
     * The first price with the corresponding "from" and "to" value
     * will be returned.
     *
     * @param array  $prices
     * @param int    $quantity
     * @param Detail $variant
     * @param array  $parameter
     *
     * @return Price
     */
    public function getPriceForQuantity(array $prices, $quantity, Detail $variant, array $parameter = []);

    /**
     * Helper function for the getVariantData function.
     * This function returns the value for the ordernumber column
     * of the s_order_basket.
     * Override this function to control an own esd handling
     * in the basket section.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return string
     */
    public function getEsdFlag(Detail $variant, $quantity, array $parameter = []);

    /**
     * Search a product variant (\Shopware\Models\Article\Detail) with the passed
     * product order number and returns it.
     *
     * @param string $orderNumber
     *
     * @return null|Detail
     */
    public function getVariantByOrderNumber($orderNumber);

    /**
     * Helper function to validate the passed product variant and the passed quantity.
     * Checks if the passed variant fulfill all requirements to add the product
     * in the current session to the basket.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return array
     */
    public function validateProduct(Detail $variant, $quantity, array $parameter = []);

    /**
     * Helper function to check if the passed variant has enough stock.
     * Returns false if the lastStock flag is set to true and
     * the passed quantity is greater than the stock value of the variant.
     * <br>
     * Notice: This function sums the already added quantity of the same variant in the basket.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return bool
     */
    public function isVariantInStock($variant, $quantity, array $parameter = []);

    /**
     * Helper function to get the summarized quantity of the basket for the passed variant.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return int Returns the summarized value of the quantity column of the s_order_basket.
     *             If the variant isn't in basket, the function return the numeric value 0.
     */
    public function getSummarizedQuantityOfVariant($variant, $quantity, array $parameter = []);

    /**
     * Helper function to fire the notify until event for "Shopware_Modules_Basket_AddArticle_Start".
     * If the event has an event listener in some plugins which returns true, the add product
     * process will be canceled.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @throws \Exception
     *
     * @return \Enlight_Event_EventArgs|null
     */
    public function fireNotifyUntilAddArticleStart(Detail $variant, $quantity, array $parameter = []);

    /**
     * Helper function to check if the passed customer group
     * can see the passed product variant.
     *
     * @param Detail $variant
     * @param Group  $customerGroup
     * @param array  $parameter
     *
     * @return bool
     */
    public function isCustomerGroupAllowed(Detail $variant, Group $customerGroup, array $parameter);

    /**
     * Helper function to check if the passed variant with the additional parameters
     * has to be add as new row or update an existing row.
     * The shopware standard checks only the order number of the passed variant.
     * If this number is already in the basket, the basket id will be returned
     * and the basket row will be updated with the new quantity and the new variant data.
     * To implement an handling for this logic, you can create an event listener
     * for this function with an Enlight_Hook_After event to modify the return value.
     * All parameters of the addProduct function are also available here.
     * To control that an existing row has to been updated, return the id of the
     * basket row.
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return bool
     */
    public function shouldAddAsNewPosition(Detail $variant, $quantity, array $parameter);

    /**
     * Helper function to create a new basket item.
     * The function expects an array with basket data.
     * All parameters of the addProduct function are also available here.
     *
     * @param array  $data
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @throws \Exception
     *
     * @return int|null the inserted data
     */
    public function createItem(array $data, Detail $variant, $quantity, array $parameter);
}
