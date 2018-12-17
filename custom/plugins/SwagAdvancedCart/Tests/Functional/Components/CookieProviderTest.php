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

namespace Shopware\SwagAdvancedCart\Tests\Functional\Components;

use SwagAdvancedCart\Components\CookieProvider;
use SwagAdvancedCart\Models\Cookie;
use SwagAdvancedCart\Services\BasketUtils;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class CookieProviderTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_createCookieProvider_should_throw_exception()
    {
        $this->expectException(\Exception::class);
        $request = new \Enlight_Controller_Request_RequestHttp();
        $response = new \Enlight_Controller_Response_ResponseHttp();

        new CookieProvider(
            new BasketUtils(
                $this->getConnection(),
                $this->getContainer()->get('shopware_storefront.context_service'),
                $this->getContainer()->get('swag_advanced_cart.dependency_provider')
            ),
            $request,
            $response,
            ''
        );
    }

    public function test_createCookieProvider_there_should_be_a_cookie()
    {
        $this->addUserAttributes();

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setCookie('BasketToken_CookieName', 'hashValue');
        Shopware()->Front()->setRequest($request);

        $cookieProvider = new CookieProvider(
            new BasketUtils(
                $this->getConnection(),
                $this->getContainer()->get('shopware_storefront.context_service'),
                $this->getContainer()->get('swag_advanced_cart.dependency_provider')
            ),
            Shopware()->Front()->Request(),
            Shopware()->Front()->Response(),
            1
        );

        $this->assertTrue($cookieProvider->getCookie() instanceof Cookie);
    }

    public function test_getCookieValueFromRequest()
    {
        $this->addUserAttributes();
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setCookie('BasketToken_CookieName', 'hashValue');
        Shopware()->Front()->setRequest($request);

        $sql = 'INSERT INTO `s_order_basket` (sessionID, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`)
VALUES
  (\'sessionId\', 0, \'Strandtuch \"Ibiza\"\', 178, \'SW10178\', 0, 1, 19.95, 16.764705882353, 19, \'2012-08-31 11:16:04\', 0,
   0, \'\', \'index\', \'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:14.0) Gecko/20100101 Firefox/14.0.1\', \'\', 1);

INSERT INTO `s_user_attributes` (`id`, `userID`, `swag_advanced_cart_cookie_name_hash`) VALUES (\'1\', \'1\', \'CookieName\');';

        $this->getConnection()->exec($sql);

        $cookieProvider = new CookieProvider(
            new BasketUtils(
                $this->getConnection(),
                $this->getContainer()->get('shopware_storefront.context_service'),
                $this->getContainer()->get('swag_advanced_cart.dependency_provider')
            ),
            Shopware()->Front()->Request(),
            Shopware()->Front()->Response(),
            1
        );

        $result = $cookieProvider->getCookieValueFromRequest();

        $this->assertSame('hashValue', $result);
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    private function addUserAttributes()
    {
        $sql = "UPDATE s_user_attributes SET swag_advanced_cart_cookie_name_hash='CookieName' WHERE userID = 1";
        $this->getConnection()->exec($sql);
    }
}
