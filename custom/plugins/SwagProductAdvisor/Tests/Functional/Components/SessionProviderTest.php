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
use SwagProductAdvisor\Components\Helper\SessionProvider;
use SwagProductAdvisor\Tests\TestCase;

class SessionProviderTest extends TestCase
{
    public function test_getHash_should_generate_new_hash()
    {
        $sessionProvider = $this->getSessionProvider();

        $advisor = $this::$helper->createAdvisor();

        $newHash = $sessionProvider->getHash($advisor->getId());

        self::assertNotNull($newHash);
        self::assertSame(32, strlen($newHash));
    }

    public function test_getHash_should_not_generate_new_hash()
    {
        $sessionProvider = $this->getSessionProvider();

        $advisor = $this::$helper->createAdvisor();

        $newHash = $sessionProvider->getHash($advisor->getId());

        self::assertNotNull($newHash);
        self::assertSame(32, strlen($newHash));

        self::assertEquals($newHash, $sessionProvider->getHash($advisor->getId()));
    }

    /**
     * @return SessionProvider
     */
    private function getSessionProvider()
    {
        return Shopware()->Container()->get('swag_product_advisor.session_provider');
    }
}
