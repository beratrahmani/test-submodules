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

namespace SwagAdvancedCart\Components;

use SwagAdvancedCart\Services\BasketUtilsInterface;

/**
 * Class StaticSavedItemUpdater
 */
class StaticSavedItemUpdater
{
    /**
     * @var array
     */
    private static $checkedArticle = [];

    /**
     * @var BasketUtilsInterface
     */
    private static $basketUtils;

    /**
     * @var int
     */
    private static $userId;

    /**
     * @var int
     */
    private static $basketId;

    /**
     * @var string
     */
    private static $cookieValue;

    /**
     * @var array[]
     */
    private static $savedItems;

    /**
     * Sets all the required static properties
     *
     * @param BasketUtilsInterface $basketUtils
     * @param $userId
     */
    public static function setRequirements(BasketUtilsInterface $basketUtils, $userId)
    {
        self::$basketUtils = $basketUtils;
        self::$userId = $userId;
        self::$cookieValue = self::$basketUtils->getCookieValueByUserId(self::$userId);
        self::$basketId = self::$basketUtils->getSavedBasketId(self::$cookieValue);
        self::$savedItems = self::$basketUtils->getSavedBasketItems(self::$basketId);
    }

    /**
     * Checks if all necessary requirements are set
     *
     * @return bool
     */
    public static function issetRequirements()
    {
        return self::$basketUtils && self::$userId;
    }

    /**
     * Updates the quantity of a saved item in a wish-list
     *
     * @param $number
     * @param $quantity
     */
    public static function updateSavedBasketItemQuantity($number, $quantity)
    {
        $orderNumber = self::findOrderNumberByBasketId($number);

        foreach (self::$savedItems as &$product) {
            if ($product['article_ordernumber'] === $orderNumber && $product['quantity'] !== $quantity) {
                $product['quantity'] = $quantity;
                self::$basketUtils->updateBasketSavedItem(self::$basketId, $orderNumber, $quantity);
                self::$checkedArticle[] = $number;
            }
        }
    }

    /**
     * Checks if an product was updated already
     *
     * @param $orderNumber
     *
     * @return bool
     */
    public static function isProductUpdated($orderNumber)
    {
        return in_array($orderNumber, self::$checkedArticle, false);
    }

    /**
     * Returns the order-number by the given basket-id.
     *
     * @param $basketId
     *
     * @return string
     */
    public static function findOrderNumberByBasketId($basketId)
    {
        return self::$basketUtils->getArticleOrderNumberFromOriginalBasket($basketId);
    }
}
