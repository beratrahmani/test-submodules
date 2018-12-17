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

namespace SwagAdvancedCart\Tests\Functional\Services;

use Shopware\Components\Random;
use SwagAdvancedCart\Components\CookieProvider;
use SwagAdvancedCart\Models\Cookie;
use SwagAdvancedCart\Services\BasketUtils;
use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class BasketUtilsTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;

    public function test_createBasket()
    {
        $expire = (new \DateTime('2017-03-02'))->getTimestamp();
        $this->getBasketUtils()->createBasket('createCookieValue', 1, $expire);
        $result = $this->readAllSavedBaskets();
        $subset = require __DIR__ . '/Results/expectedArraySubsetCreateBasket.php';

        $this->assertArraySubset($subset, $result);
    }

    public function test_getSavedBasketId()
    {
        $this->getConnection()->exec("INSERT INTO s_order_basket_saved 
          (id, cookie_value, user_id, shop_id, expire, modified, name, published) 
          VALUES 
          (333, 'createCookieValue', 1, 1, '1970-01-01', '0000-00-00 00:00:00', 'handMade', 0)");

        $result = $this->getBasketUtils()->getSavedBasketId('createCookieValue');

        $this->assertEquals(333, $result);
    }

    public function test_getCookieValueByUserId()
    {
        $this->getConnection()->exec("INSERT INTO s_order_basket_saved 
          (id, cookie_value, user_id, shop_id, expire, modified, name, published) 
          VALUES 
          (333, 'getCookieValueByUserId', 1, 1, '1970-01-01', '0000-00-00 00:00:00', NULL, 0)");

        $result = $this->getBasketUtils()->getCookieValueByUserId(1);

        $this->assertEquals('getCookieValueByUserId', $result);
    }

    public function test_mergeBaskets_session_basket()
    {
        $this->loginCustomer();

        $session = Shopware()->Container()->get('session');
        $session->offsetSet('oldSessionId', 'oldSessionId');

        $this->createUnknownUserSessionCart();

        $cookieProvider = new CookieProviderMock($this->getBasketUtils(), 1);
        $this->getBasketUtils()->mergeBaskets(1, $session, $cookieProvider);

        $items = $this->readAllSavedBaskets();
        $subset = require __DIR__ . '/Results/expectedArraySubsetAfterMergeBaskets.php';

        $this->assertEquals(1, count($items));
        $this->assertArraySubset($subset, $items);
    }

    public function test_mergeBaskets_session_and_old_baskets()
    {
        $session = Shopware()->Session();
        $this->loginCustomer();
        $session->offsetSet('oldSessionId', 'oldSessionId');

        $request = new \Enlight_Controller_Request_RequestHttp();
        Shopware()->Front()->setRequest($request);

        $response = new \Enlight_Controller_Response_ResponseHttp();
        Shopware()->Front()->setResponse($response);

        $this->createUnknownUserSessionCart();
        $this->createOldUserCart();

        $cookieProvider = new CookieProviderMock($this->getBasketUtils(), 1);
        $this->getBasketUtils()->mergeBaskets(1, $session, $cookieProvider);

        $items = $this->readAllSavedBaskets();
        $subset = require __DIR__ . '/Results/expectedArraySubsetAfterMergeBaskets.php';

        $this->assertEquals(1, count($items));
        $this->assertArraySubset($subset, $items);
    }

    public function test_increasingArticleQuantity()
    {
        $this->createCart();
        $this->getBasketUtils()->increasingArticleQuantity(12, 169111, 'SW10239');

        $resultQuantity = $this->getConnection()->fetchColumn(
            'SELECT quantity FROM s_order_basket_saved_items WHERE basket_id = :basketId AND article_ordernumber = :orderNumber',
            ['basketId' => 169111, 'orderNumber' => 'SW10239']
        );

        $this->assertSame('13', $resultQuantity);
    }

    public function test_deleteBasketSavedItem()
    {
        $this->createCart();
        $this->getBasketUtils()->deleteBasketSavedItem(169111, 'SW10239');

        $result = $this->getConnection()->fetchColumn(
            'SELECT 1 FROM s_order_basket_saved_items WHERE basket_id = :basketId AND article_ordernumber = :orderNumber',
            ['basketId' => 169111, 'orderNumber' => 'SW10239']
        );

        $this->assertFalse($result);
    }

    public function test_getArticleDataFromOriginalBasketBySessionId()
    {
        $this->createCart();
        $sessionId = Shopware()->Session()->get('sessionId');
        $this->getConnection()->exec(
            "UPDATE s_order_basket 
              SET sessionID = '$sessionId' 
              WHERE id IN (1177111, 1178111, 1179111, 1180111, 1185111)"
        );

        $result = $this->getBasketUtils()->getArticleDataFromOriginalBasketBySessionId();

        $expectedResult = [
            [
                'ordernumber' => 'SW10239',
                'quantity' => '1',
            ],
            [
                'ordernumber' => 'SW10038',
                'quantity' => '1',
            ],
            [
                'ordernumber' => 'SW10036',
                'quantity' => '1',
            ],
            [
                'ordernumber' => 'SW10083',
                'quantity' => '1',
            ],
        ];

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_saveCookieNameHashToUserAttributes()
    {
        $nameHash = 'newCookieNameHash';
        $this->getBasketUtils()->saveCookieNameHashToUserAttributes(1, $nameHash);

        $result = $this->getConnection()->fetchColumn(
            'SELECT swag_advanced_cart_cookie_name_hash FROM s_user_attributes WHERE userID = 1'
        );

        $this->assertEquals($nameHash, $result);
    }

    public function test_getSavedBasketItemIds()
    {
        $this->createCart();
        $result = $this->getBasketUtils()->getSavedBasketItems(169111);
        $expectedResult = [
            [
                'id' => '323111',
                'basket_id' => '169111',
                'article_ordernumber' => 'SW10239',
                'quantity' => '1',
            ],
            [
                'id' => '324111',
                'basket_id' => '169111',
                'article_ordernumber' => 'SW10038',
                'quantity' => '1',
            ],
            [
                'id' => '325111',
                'basket_id' => '169111',
                'article_ordernumber' => 'SW10036',
                'quantity' => '1',
            ],
            [
                'id' => '326111',
                'basket_id' => '169111',
                'article_ordernumber' => 'SW10083',
                'quantity' => '1',
            ],
        ];

        $this->assertArraySubset($expectedResult, $result);
    }

    private function createCart()
    {
        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/createWishlist.sql'));
    }

    private function readAllSavedBaskets()
    {
        return $this->getConnection()->createQueryBuilder()
            ->select('*')
            ->from('s_order_basket_saved')
            ->execute()
            ->fetchAll();
    }

    private function createOldUserCart()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/oldUserCart.sql');
        $this->getConnection()->exec($sql);
    }

    private function createUnknownUserSessionCart()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/sessionCart.sql');
        $this->getConnection()->exec($sql);
    }

    private function getBasketUtils()
    {
        $container = self::getKernel()->getContainer();

        return new BasketUtils(
            $container->get('dbal_connection'),
            $container->get('shopware_storefront.context_service'),
            $container->get('swag_advanced_cart.dependency_provider')
        );
    }

    private function getConnection()
    {
        return self::getKernel()->getContainer()->get('dbal_connection');
    }
}

class CookieProviderMock extends CookieProvider
{
    /**
     * @var Cookie
     */
    private $cookie;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var \Enlight_Controller_Response_ResponseHttp
     */
    private $response;

    /**
     * @var \Enlight_Controller_Request_Request
     */
    private $request;

    /**
     * @var BasketUtils
     */
    private $basketUtils;

    /**
     * Initialise the cookie provider
     *
     * @param BasketUtils $basketUtils
     * @param int         $userId
     *
     * @throws \Exception
     */
    public function __construct(BasketUtils $basketUtils, $userId)
    {
        if (!$userId) {
            throw new \Exception('CookieProvider - Line: ' . __LINE__ . ' - NO USER-ID');
        }
        $this->basketUtils = $basketUtils;
        $this->userId = $userId;
        $this->response = Shopware()->Front()->Response();
        $this->request = Shopware()->Front()->Request();
        $this->instanceCookie();
    }

    /**
     * @return Cookie
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * sets a new browser cookie by the data provided in the cookie object
     */
    public function setCookie()
    {
        if (null === $this->cookie->getCookieValue()) {
            $this->cookie->setCookieValue($this->generateCookieValue());
        }
        $this->response->setCookie(
            $this->cookie->getCookieName(),
            $this->cookie->getCookieValue(),
            $this->cookie->getExpireTime()
        );
    }

    /**
     * returns the cookie value from a browser cookie
     *
     * @return mixed
     */
    public function getCookieValueFromRequest()
    {
        return $this->request->getCookie($this->cookie->getCookieName());
    }

    /**
     * Creates new cookie with browser cookie data
     * Creates a whole new cookie, if no browser cookie is set
     */
    private function instanceCookie()
    {
        $cookieNameHash = $this->getCookieNameHash();
        $cookieValue = $this->getCookieValueFromDatabase();
        $this->cookie = new CookieMock($cookieNameHash);
        if ($cookieValue) {
            $this->cookie->setCookieValue($cookieValue);
        }
    }

    /**
     * returns the cookie value from the database
     *
     * @return string
     */
    private function getCookieValueFromDatabase()
    {
        return $this->basketUtils->getCookieValueByUserId($this->userId);
    }

    /**
     * returns the cookie name hash for the given user
     * creates a new one if necessary
     *
     * @return string|null
     */
    private function getCookieNameHash()
    {
        $cookieNameHash = $this->basketUtils->getUserCookieNameHashByUserId($this->userId);
        if (!$cookieNameHash) {
            $cookieNameHash = $this->generateCookieNameHash();
            $this->basketUtils->saveCookieNameHashToUserAttributes($this->userId, $cookieNameHash);
        }

        return $cookieNameHash;
    }

    /**
     * generates a new cookie name hash
     *
     * @return string
     */
    private function generateCookieNameHash()
    {
        return $this->generateUniqueHash();
    }

    /**
     * generates a new cookie value
     *
     * @return string
     */
    private function generateCookieValue()
    {
        return $this->generateUniqueHash(50);
    }

    /**
     * generates a unique hash with the given length
     *
     * @param int $length
     *
     * @return string
     */
    private function generateUniqueHash($length = 32)
    {
        return Random::getAlphanumericString($length);
    }
}

class CookieMock extends Cookie
{
    const COOKIE_PREFIX = 'BasketToken';

    /**
     * @var string
     */
    private $cookieName;

    /**
     * @var string
     */
    private $cookieNameHash;

    /**
     * @var string
     */
    private $cookieValue;

    /**
     * @var int
     */
    private $expireTime;

    /**
     * creates a new cookie with the given cookie name hash
     *
     * @param $cookieNameHash
     */
    public function __construct($cookieNameHash)
    {
        $this->cookieNameHash = $cookieNameHash;
        $this->setCookieName();
        $this->createCookieValue();
        $this->expireTime = $this->generateExpireTime();
    }

    /**
     * returns the cookie name
     *
     * @return string
     */
    public function getCookieName()
    {
        return $this->cookieName;
    }

    /**
     * returns the cookie value
     *
     * @return string
     */
    public function getCookieValue()
    {
        return $this->cookieValue;
    }

    /**
     * sets the cookie value
     *
     * @param $cookieValue
     *
     * @return $this
     */
    public function setCookieValue($cookieValue)
    {
        $this->cookieValue = $cookieValue;

        return $this;
    }

    /**
     * returns the expire time of the cookie
     *
     * @return int
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * sets the cookie name
     */
    private function setCookieName()
    {
        $this->cookieName = self::COOKIE_PREFIX . '_' . $this->cookieNameHash;
    }

    /**
     * gets the cookie value from the browser cookie
     */
    private function createCookieValue()
    {
        $this->cookieValue = 'cookieValue';
    }

    /**
     * generates the expire time of the cookie
     *
     * @return int
     */
    private function generateExpireTime()
    {
        return time() + (86400 * Shopware()->Config()->get('expireDateInDays'));
    }
}
