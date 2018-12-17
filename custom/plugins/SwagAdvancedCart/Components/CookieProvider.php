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

use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;
use Shopware\Components\Random;
use SwagAdvancedCart\Models\Cookie;
use SwagAdvancedCart\Services\BasketUtilsInterface;

/**
 * Class CookieProvider
 *
 * provides all necessary operations to handle cookies
 */
class CookieProvider
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
     * @var Enlight_Controller_Request_Request
     */
    private $request;

    /**
     * @var BasketUtilsInterface
     */
    private $basketUtils;

    /**
     * Initialise the cookie provider
     *
     * @param BasketUtilsInterface                 $basketUtils
     * @param Enlight_Controller_Request_Request   $request
     * @param Enlight_Controller_Response_Response $response
     * @param int                                  $userId
     *
     * @throws \Exception
     */
    public function __construct(
        BasketUtilsInterface $basketUtils,
        Enlight_Controller_Request_Request $request,
        Enlight_Controller_Response_Response $response,
        $userId
    ) {
        if (!$userId) {
            throw new \Exception('CookieProvider - Line: ' . __LINE__ . ' - NO USER-ID');
        }

        $this->basketUtils = $basketUtils;
        $this->userId = $userId;
        $this->request = $request;
        $this->response = $response;
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
        $this->cookie = new Cookie($cookieNameHash);
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
