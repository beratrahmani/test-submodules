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

namespace SwagBundle\Components;

use Shopware\Models\Article\Detail;

interface BundleBasketInterface
{
    /**
     * Constant which defines the basket item mode for bundle discounts
     */
    const BUNDLE_DISCOUNT_BASKET_MODE = 10;

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
     * Global interface to add a single product to the basket.
     * The passed order number used as identifier for the product.
     * The passed quantity is used to identify how many times the customer want to buy the product.
     *
     * <pre>
     * To add a product to the shopware basket, shopware checks the following conditions:
     * 1. The passed order number has to be a valid order number which defined over the s_articles_details table.
     * 2. The product and the variant of the passed order number has to been activated
     *    (active column in the s_articles and s_articles_details).
     * 3. The current customer group must be enabled for the selected product.
     * 4. The Shopware_Modules_Basket_AddArticle_Start notifyUntil event should not return TRUE
     * 5. The variant stock has to be greater or equal than the sum of the quantity of the basket for the current
     *    customer session and the passed quantity for the passed product.
     * 6. The product must have defined a price.
     * </pre>
     *
     * @param string $orderNumber the order number of the product variant
     * @param int    $quantity    how many unit of the variant has to be added
     * @param array  $parameter   An optional array of process parameters for the bundle plugin
     *
     * @return array
     */
    public function addProduct($orderNumber, $quantity = 1, array $parameter = []);

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
     * Helper function to check if the passed variant with the additional parameters
     * has to be add as new row or update an existing row.
     * The shopware standard checks only the order number of the passed variant.
     * If this number is already in the basket, the basket id will be returned
     * and the basket row will be updated with the new quantity and the new variant data.
     * All parameters of the addProduct function are also available here.
     * To control that an existing row has to been updated, return the id of the basket row.
     *
     * @param Detail $variant
     * @param array  $parameter
     *
     * @return true|int
     */
    public function shouldAddAsNewPosition(Detail $variant, array $parameter);

    /**
     * Helper function to get the variant data for the new basket position
     *
     * @param Detail $variant
     * @param int    $quantity
     * @param array  $parameter
     *
     * @return array
     */
    public function getVariantCreateData(Detail $variant, $quantity, array $parameter = []);

    /**
     * Helper function to get the variant data for the updated basket position
     *
     * @param int   $quantity
     * @param array $parameter
     *
     * @return array
     */
    public function getVariantUpdateData($quantity, array $parameter = []);

    /**
     * Helper function to get the summarized quantity of the basket for the passed variant.
     *
     * @param Detail $variant
     *
     * @return int Returns the summarized value of the quantity column of the s_order_basket.
     *             If the variant isn't in basket, the function return the numeric value 0.
     */
    public function getSummarizedQuantityOfVariant(Detail $variant);
}
