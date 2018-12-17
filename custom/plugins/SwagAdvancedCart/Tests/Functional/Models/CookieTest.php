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

namespace SwagAdvancedCart\Tests\Functional\Models;

use SwagAdvancedCart\Models\Cookie;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class CookieTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_create_cookie_and_get_name()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setCookie('BasketToken_cookieName', 'cookieNameHash');
        Shopware()->Front()->setRequest($request);
        $cookie = new Cookie('cookieName');

        $this->assertEquals('BasketToken_cookieName', $cookie->getCookieName());
    }

    public function test_get_value()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setCookie('BasketToken_cookieName', 'cookieNameHash');
        Shopware()->Front()->setRequest($request);
        $cookie = new Cookie('cookieName');

        $this->assertEquals('cookieNameHash', $cookie->getCookieValue());
    }

    public function test_set_cookie_value()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setCookie('BasketToken_cookieName', 'cookieNameHash');
        Shopware()->Front()->setRequest($request);
        $cookie = new Cookie('cookieName');
        $cookie->setCookieValue('newCookieValue');

        $this->assertEquals('newCookieValue', $cookie->getCookieValue());
    }

    public function test_get_expire_time()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setCookie('BasketToken_cookieName', 'cookieNameHash');
        Shopware()->Front()->setRequest($request);

        $cookie = new Cookie('cookieName');

        $this->assertNotNull($cookie->getExpireTime());
    }
}
