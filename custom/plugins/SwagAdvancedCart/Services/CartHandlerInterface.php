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

namespace SwagAdvancedCart\Services;

interface CartHandlerInterface
{
    /**
     * Creates a new wish-list when saving the current basket as a wish-list.
     *
     * @param string $name
     * @param $published
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function saveCart($name, $published);

    /**
     * Helper method to add an article to an existing list or create a new list on the detail-page.
     *
     * @param array $postData
     *
     * @throws \RuntimeException
     *
     * @return array|void
     */
    public function addToList(array $postData);

    /**
     * Creates wish-list
     *
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return int
     */
    public function createWishList($name);

    /**
     * Prepares the cart for the modal-window on the detail- or listing-page.
     *
     * @param array $carts
     *
     * @return array
     */
    public function prepareCartForModal(array $carts);

    /**
     * Get all cart-items by the order-numbers.
     * This way we automatically filter inactive or disabled articles.
     *
     * @param array $orderNumbers
     * @param int   $basketId
     *
     * @return array
     */
    public function getCartItemsByOrderNumbers(array $orderNumbers, $basketId = null);

    /**
     * Check if user have a basket with this name
     *
     * @param string $name
     * @param int    $userID
     *
     * @throws \RuntimeException
     */
    public function checkIfBasketNameExists($name, $userID);
}
