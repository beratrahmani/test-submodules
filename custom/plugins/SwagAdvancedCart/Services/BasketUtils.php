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

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace as Session;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagAdvancedCart\Components\CookieProvider;
use SwagAdvancedCart\Services\Dependencies\DependencyProviderInterface;

/**
 * Class BasketUtils
 *
 * provides all necessary database operations used in AdvancedCart
 */
class BasketUtils implements BasketUtilsInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * Initialize the basket utils by setting the database connection and the shop id
     *
     * @param Connection                  $connection
     * @param ContextServiceInterface     $contextService
     * @param DependencyProviderInterface $dependencyProvider
     */
    public function __construct(Connection $connection, ContextServiceInterface $contextService, DependencyProviderInterface $dependencyProvider)
    {
        $this->connection = $connection;
        $this->contextService = $contextService;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function createBasket($cookieValue, $userId, $expire)
    {
        $expireInt = $expire;
        $expire = new \DateTime();
        $expire->setTimestamp($expireInt);
        $shopId = $this->getShopId();

        return $this->connection->executeQuery(
            'INSERT INTO s_order_basket_saved (cookie_value, user_id, expire, shop_id) VALUES ( :cookieValue, :userId, :expire, :shopId )',
            [
                'cookieValue' => $cookieValue,
                'userId' => $userId,
                'expire' => $expire->format('Y-m-d'),
                'shopId' => $shopId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSavedBasketId($cookieValue)
    {
        $shopId = $this->getShopId();

        $result = $this->connection->fetchColumn(
            'SELECT id FROM s_order_basket_saved WHERE cookie_value = :cookieValue AND shop_id = :shopId',
            [
                'cookieValue' => $cookieValue,
                'shopId' => $shopId,
            ]
        );

        return (int) $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieValueByUserId($userId)
    {
        $shopId = $this->getShopId();

        return $this->connection->fetchColumn(
            'SELECT cookie_value FROM s_order_basket_saved WHERE user_id = :userID AND ISNULL(name) AND shop_id = :shopId',
            [
                'userID' => $userId,
                'shopId' => $shopId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function deleteBasketSaved($basketId)
    {
        $shopId = $this->getShopId();

        $this->connection->executeQuery(
            $sql = 'DELETE FROM `s_order_basket_saved` WHERE id = :basketId AND shop_id = :shopId;',
            [
                'basketId' => $basketId,
                'shopId' => $shopId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadWishList($userID)
    {
        $shopId = $this->getShopId();

        return $this->connection->fetchAll(
            'SELECT * FROM s_order_basket_saved WHERE user_id = :userId AND `name` IS NOT NULL AND shop_id = :shopId',
            [
                'userId' => $userID,
                'shopId' => $shopId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSavedBasketItems($basketId)
    {
        return $this->connection->fetchAll(
            'SELECT * FROM s_order_basket_saved_items WHERE basket_id = :basketId',
            ['basketId' => $basketId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSavedBasketItemsByOrderNumber($orderNumber)
    {
        return $this->connection->fetchAll(
            'SELECT * FROM s_order_basket_saved_items WHERE article_ordernumber = :orderNumber',
            [
                'orderNumber' => $orderNumber,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createBasketItem($basketId, $orderNumber, $quantity)
    {
        $this->connection->executeQuery(
            'INSERT INTO s_order_basket_saved_items (basket_id, article_ordernumber, quantity) VALUES ( :basketId, :orderNumber, :quantity)',
            [
                'basketId' => $basketId,
                'orderNumber' => $orderNumber,
                'quantity' => $quantity,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateBasketSavedItem($basketId, $orderNumber, $quantity)
    {
        $this->connection->executeQuery(
            'UPDATE s_order_basket_saved_items SET quantity = :quantity WHERE basket_id = :basketId AND article_ordernumber = :orderNumber',
            [
                'quantity' => $quantity,
                'basketId' => $basketId,
                'orderNumber' => $orderNumber,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function increasingArticleQuantity($quantity, $basketId, $orderNumber)
    {
        $this->connection->executeQuery(
            'UPDATE s_order_basket_saved_items SET quantity = quantity + :quantity WHERE basket_id = :basketId AND article_ordernumber = :orderNumber',
            [
                'quantity' => $quantity,
                'basketId' => $basketId,
                'orderNumber' => $orderNumber,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBasketSavedItem($basketId, $orderNumber)
    {
        $this->connection->executeQuery(
            'DELETE FROM s_order_basket_saved_items WHERE basket_id = :basketId AND article_ordernumber = :orderNumber',
            [
                'basketId' => $basketId,
                'orderNumber' => $orderNumber,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBasketSavedItems($basketId)
    {
        $this->connection->executeQuery(
            'DELETE FROM s_order_basket_saved_items WHERE basket_id = :basketId',
            [
                'basketId' => $basketId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function checkIfOrderNumberExists($basketId, $orderNumber)
    {
        return $this->connection->fetchColumn(
            'SELECT article_ordernumber FROM s_order_basket_saved_items WHERE basket_id = :basketId AND article_ordernumber = :orderNumber',
            [
                'basketId' => $basketId,
                'orderNumber' => $orderNumber,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getArticleOrderNumberFromOriginalBasket($originalBasketId)
    {
        return $this->connection->fetchColumn(
            'SELECT ordernumber FROM s_order_basket WHERE id = :originalBasketId',
            ['originalBasketId' => $originalBasketId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getArticleDataFromOriginalBasketBySessionId()
    {
        $session = $this->dependencyProvider->getSession();
        $sessionID = $session->get('sessionId');

        return $this->connection->fetchAll(
            'SELECT ordernumber, quantity FROM s_order_basket WHERE sessionID = :sessionId AND modus = 0',
            [
                'sessionId' => $sessionID,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function saveCookieNameHashToUserAttributes($userId, $cookieNameHash)
    {
        $this->connection->executeQuery(
            'INSERT INTO s_user_attributes (userID, swag_advanced_cart_cookie_name_hash) VALUES (:userId, :cookieName)
              ON DUPLICATE KEY UPDATE swag_advanced_cart_cookie_name_hash = :cookieName;',
            [
                'cookieName' => $cookieNameHash,
                'userId' => $userId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUserCookieNameHashByUserId($userId)
    {
        $sql = 'SELECT `swag_advanced_cart_cookie_name_hash` FROM s_user_attributes WHERE userID = :userId';

        return $this->connection->fetchColumn(
            $sql,
            ['userId' => $userId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateBasketOnUserLogin($basketId, $newCookieValue, $userId, $expire)
    {
        $dateTime = new \DateTime();
        $modified = $dateTime->format('Y-m-d H:i:s');

        $expireTime = new \DateTime();
        $expireTime->setTimestamp($expire);
        $expire = $expireTime->format('Y-m-d');

        return $this->connection->executeQuery(
            'UPDATE s_order_basket_saved SET cookie_value = :newCookieValue, user_id = :userId, expire = :expireTime, modified = :modified WHERE id = :basketId',
            [
                'newCookieValue' => $newCookieValue,
                'userId' => $userId,
                'expireTime' => $expire,
                'modified' => $modified,
                'basketId' => $basketId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSavedBasketItemIds($basketId)
    {
        $returnValue = $this->connection->fetchAll(
            'SELECT id FROM s_order_basket_saved_items WHERE basket_id = :basketId',
            ['basketId' => $basketId]
        );

        if (!$returnValue) {
            $returnValue = [];
        }

        return $returnValue;
    }

    /**
     * {@inheritdoc}
     */
    public function updateBasketSavedItemBasketIdById($basketId, array $idList)
    {
        return $this->connection->executeQuery(
            'UPDATE s_order_basket_saved_items SET basket_id = :basketId WHERE basket_id IN (:idList)',
            [
                'basketId' => $basketId,
                'idList' => implode(',', $idList),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBaskets(array $basketIds)
    {
        return $this->connection->executeQuery(
            'DELETE FROM s_order_basket_saved WHERE id IN (:list)',
            [
                'list' => implode(',', $basketIds),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSavedBasketIds($cookieValue)
    {
        $sql = 'SELECT id FROM s_order_basket_saved WHERE cookie_value = :cookieValue';

        $result = $this->connection->fetchAll(
            $sql,
            ['cookieValue' => $cookieValue]
        );

        if (!empty($result)) {
            return $result[0];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeBaskets($userId, Session $session, CookieProvider $cookieProvider)
    {
        $oldSessionId = $session->offsetGet('oldSessionId');
        $cookieValue = $cookieProvider->getCookie()->getCookieValue();
        $cookieExpireTime = $cookieProvider->getCookie()->getExpireTime();
        $sessionBasketId = $this->getSavedBasketId($oldSessionId);

        $unusedSessionBasket = $this->getSavedBasketId($session->offsetGet('sessionId'));
        if ($unusedSessionBasket) {
            $this->deleteBasketSavedItems($unusedSessionBasket);
            $this->deleteBasketSaved($unusedSessionBasket);
        }

        if (!$sessionBasketId) {
            return;
        }

        $savedBasketIds = $this->getSavedBasketIds($cookieValue);
        $this->updateBasketOnUserLogin($sessionBasketId, $cookieValue, $userId, $cookieExpireTime);

        if (!$savedBasketIds) {
            return;
        }

        $this->deleteBaskets($savedBasketIds);
        $this->updateBasketSavedItemBasketIdById($sessionBasketId, $savedBasketIds);
    }

    /**
     * Returns the current shop id
     *
     * @return int|null|string
     */
    private function getShopId()
    {
        $shop = $this->contextService->getShopContext()->getShop();
        if ($shop) {
            return $shop->getId();
        }

        return $this->getDefaultShop();
    }

    /**
     * @return string
     */
    private function getDefaultShop()
    {
        return $this->connection->fetchColumn('SELECT id FROM s_core_shops WHERE main_id IS NULL');
    }
}
