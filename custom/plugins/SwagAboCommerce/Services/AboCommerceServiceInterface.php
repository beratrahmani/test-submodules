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

use Shopware\Models\Article\Detail;

interface AboCommerceServiceInterface
{
    /**
     * Returns the abo-data for the specified product detail model.
     *
     * @param Detail $detail
     *
     * @return array
     */
    public function getAboCommerceDataSelectedProduct(Detail $detail);

    /**
     * Inserts the discount for the given basket item.
     *
     * @param Detail $variant
     * @param array  $aboProduct
     * @param array  $basketItem
     */
    public function insertDiscountForProducts(Detail $variant, array $aboProduct, array $basketItem);

    /**
     * Search an product variant (\Shopware\Models\Article\Detail) with the passed
     * product order number and returns it.
     *
     * @param string $orderNumber
     *
     * @return null|Detail
     */
    public function getVariantByOrderNumber($orderNumber);

    /**
     * Updates every discount item in the basket.
     */
    public function updateBasketDiscount();

    /**
     * Return whether or not a abo-product is in the basket.
     *
     * @return bool
     */
    public function isAboCommerceProductInBasket();

    /**
     * Return whether or not a specific abo-product configuration in the the basket.
     *
     * @param string $orderNumber
     *
     * @return array|null
     */
    public function isAboCommerceConfigurationInBasket($orderNumber);

    /**
     * Registers the shop resource.
     *
     * @param int    $shopId
     * @param string $currency
     */
    public function registerShop($shopId, $currency);

    /**
     * This method fetches all needed data for the follow up order and adds it to the view.
     *
     * @param array $aboOrder
     * @param array $order
     */
    public function registerOrder(array $aboOrder, array $order);

    /**
     * Returns user data according to the given order id.
     *
     * @param int        $orderId
     * @param array|null $aboOrder
     *
     * @return array
     */
    public function getUserData($orderId, array $aboOrder = null);

    /**
     * Sets the AboCommerce flags of the provided array of products.
     * Returns an array with the merged data.
     *
     * @param array $products
     *
     * @return array
     */
    public function setAboCommerceFlagForProducts(array $products = []);

    /**
     * Returns a value indicating if an order with the specified identifier exists.
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function orderExists($orderId);

    /**
     * A helper function returning a boolean indicating whether the provided orderId belongs to an abonnement.
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function isAboOrder($orderId);

    /**
     * Returns a value indicating whether or not the specified order is the initial abo order.
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function isInitialOrder($orderId);

    /**
     * @param int $productId
     *
     * @return int|bool
     */
    public function getIsAboExclusive($productId);
}
