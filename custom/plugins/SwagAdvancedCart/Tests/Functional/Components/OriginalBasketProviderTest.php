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

use SwagAdvancedCart\Components\OriginalBasketProvider;
use SwagAdvancedCart\Services\BasketUtils;
use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class OriginalBasketProviderTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;

    public function test_onUserLogout_there_should_be_no_shopware_basket()
    {
        $sessionId = 'sessionId';
        Shopware()->Session()->offsetSet('sessionId', $sessionId);
        $this->createOriginalBasket();
        $this->getOriginalBasketProvider()->onUserLogout();

        $result = $this->getOriginalBasket($sessionId);
        $this->assertEmpty($result);
    }

    public function test_isArticleInBasket_should_be_false()
    {
        $sessionId = 'sessionId';
        Shopware()->Session()->offsetSet('sessionId', $sessionId);

        $reflectionClass = new \ReflectionClass(OriginalBasketProvider::class);
        $method = $reflectionClass->getMethod('isArticleInBasket');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->getOriginalBasketProvider(), ['SW10178']);

        $this->assertFalse($result);
    }

    public function test_isArticleInBasket_should_be_true()
    {
        $sessionId = 'sessionId';
        Shopware()->Session()->offsetSet('sessionId', $sessionId);
        $this->createOldUserCart();

        $reflectionClass = new \ReflectionClass(OriginalBasketProvider::class);
        $method = $reflectionClass->getMethod('isArticleInBasket');
        $method->setAccessible(true);

        // For compatibility with SwagLiveShopping in Shopware 5.2
        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestTestCase());

        Shopware()->Modules()->Basket()->sAddArticle('SW10178');

        $result = $method->invokeArgs($this->getOriginalBasketProvider(), ['SW10178']);

        $this->assertTrue($result);
    }

    /**
     * @return \Shopware\Components\DependencyInjection\Container
     */
    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    /**
     * @return \Doctrine\DBAL\Connection|mixed
     */
    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    /**
     * @return OriginalBasketProvider
     */
    private function getOriginalBasketProvider()
    {
        return new OriginalBasketProvider(
            new BasketUtils(
                $this->getConnection(),
                $this->getContainer()->get('shopware_storefront.context_service'),
                $this->getContainer()->get('swag_advanced_cart.dependency_provider')
            ),
            $this->getConnection(),
            $this->getContainer()->get('session'),
            $this->getContainer()->get('swag_advanced_cart.dependency_provider')
        );
    }

    /**
     * @param string $sessionId
     *
     * @return array
     */
    private function getOriginalBasket($sessionId)
    {
        return $this->getConnection()->fetchAll(
            'SELECT id FROM s_order_basket WHERE sessionID LIKE :sessionId;',
            ['sessionId' => $sessionId]
        );
    }

    private function createOriginalBasket()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/createOriginalBasket.sql');
        $this->getConnection()->exec($sql);
    }

    private function createOldUserCart()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/oldUserCart.sql');
        $this->getConnection()->exec($sql);
    }
}
