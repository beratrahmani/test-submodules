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

use Enlight_Components_Session_Namespace as Session;
use SwagAdvancedCart\Components\CookieProvider;

interface BasketUtilsInterface
{
    /**
     * creates new saved basket
     *
     * @param string $cookieValue
     * @param int    $userId
     * @param string $expire
     *
     * @return \Zend_Db_Statement_Pdo
     */
    public function createBasket($cookieValue, $userId, $expire);

    /**
     * returns saved basket id by the given cookie value
     *
     * @param string $cookieValue
     *
     * @return int
     */
    public function getSavedBasketId($cookieValue);

    /**
     * returns the cookie value of the given user
     *
     * @param int $userId
     *
     * @return string
     */
    public function getCookieValueByUserId($userId);

    /**
     * deletes a saved basket by its id and shop
     *
     * @param int $basketId
     */
    public function deleteBasketSaved($basketId);

    /**
     * returns all wish lists for the given user
     * wish lists are all saved baskets with a name
     *
     * @param int $userID
     *
     * @return array
     */
    public function loadWishList($userID);

    /**
     * returns saved basket items by their basket id
     *
     * @param int $basketId
     *
     * @return array
     */
    public function getSavedBasketItems($basketId);

    /**
     * returns all saved items by the given order number
     *
     * @param string $orderNumber
     *
     * @return array
     */
    public function getSavedBasketItemsByOrderNumber($orderNumber);

    /**
     * creates a new saved item
     *
     * @param string $basketId
     * @param string $orderNumber
     * @param int    $quantity
     */
    public function createBasketItem($basketId, $orderNumber, $quantity);

    /**
     * updates a saved item by the basket id and order number
     *
     * @param int    $quantity
     * @param int    $basketId
     * @param string $orderNumber
     */
    public function updateBasketSavedItem($basketId, $orderNumber, $quantity);

    /**
     * increases the quantity of a saved item
     *
     * @param int    $quantity
     * @param int    $basketId
     * @param string $orderNumber
     */
    public function increasingArticleQuantity($quantity, $basketId, $orderNumber);

    /**
     * deletes a saved item by basket id and order number
     *
     * @param int    $basketId
     * @param string $orderNumber
     */
    public function deleteBasketSavedItem($basketId, $orderNumber);

    /**
     * deletes all items by the the given basket id
     *
     * @param int $basketId
     */
    public function deleteBasketSavedItems($basketId);

    /**
     * checks if an item with the given order number already exists in the saved basket
     *
     * @param int    $basketId
     * @param string $orderNumber
     *
     * @return string
     */
    public function checkIfOrderNumberExists($basketId, $orderNumber);

    /**
     * returns product orderNumber by original basket position
     *
     * @param int $originalBasketId
     *
     * @return string
     */
    public function getArticleOrderNumberFromOriginalBasket($originalBasketId);

    /**
     * returns order number and quantity of items in the current original basket
     *
     * @return array
     */
    public function getArticleDataFromOriginalBasketBySessionId();

    /**
     * saves unique cookie name hash for the given user
     *
     * @param int    $userId
     * @param string $cookieNameHash
     */
    public function saveCookieNameHashToUserAttributes($userId, $cookieNameHash);

    /**
     * returns the cookie name hash of the given user
     *
     * @param int $userId
     *
     * @return string|null
     */
    public function getUserCookieNameHashByUserId($userId);

    /**
     * @param int    $basketId
     * @param string $newCookieValue
     * @param int    $userId
     * @param int    $expire
     *
     * @return \Zend_Db_Statement_Pdo
     */
    public function updateBasketOnUserLogin($basketId, $newCookieValue, $userId, $expire);

    /**
     * @param int $basketId
     *
     * @return array
     */
    public function getSavedBasketItemIds($basketId);

    /**
     * @param string $basketId
     * @param array  $idList
     *
     * @return \Zend_Db_Statement_Pdo
     */
    public function updateBasketSavedItemBasketIdById($basketId, array $idList);

    /**
     * @param array $basketIds
     *
     * @return \Zend_Db_Statement_Pdo
     */
    public function deleteBaskets(array $basketIds);

    /**
     * @param string $cookieValue
     *
     * @return array | null
     */
    public function getSavedBasketIds($cookieValue);

    /**
     * gets all items from the original basket and session basket
     * gets the saved basket id, creates a new saved basket if necessary
     * deletes the saved items from this saved basket
     * puts all items from the original basket into the saved basket
     *
     * @param int            $userId
     * @param Session        $session
     * @param CookieProvider $cookieProvider
     */
    public function mergeBaskets($userId, Session $session, CookieProvider $cookieProvider);
}
