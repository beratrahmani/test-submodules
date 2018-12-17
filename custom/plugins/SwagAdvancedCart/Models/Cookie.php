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

namespace SwagAdvancedCart\Models;

/**
 * Class Cookie
 *
 * contains all infos about the cookie
 */
class Cookie
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
        $this->cookieValue = Shopware()->Front()->Request()->getCookie($this->getCookieName());
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
