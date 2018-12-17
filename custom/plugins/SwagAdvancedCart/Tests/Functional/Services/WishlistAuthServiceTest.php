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

namespace Shopware\SwagAdvancedCart\Tests\Functional\Services;

use SwagAdvancedCart\Services\WishlistAuthService;
use SwagAdvancedCart\Services\WishlistAuthServiceInterface;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class WishlistAuthServiceTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_authenticateById_should_return_false()
    {
        $this->createWishList();

        $authService = $this->getAuthService();

        $result = $authService->authenticateById(1);

        $this->assertFalse($result);
    }

    public function test_authenticateById_should_return_true()
    {
        $this->createWishList();

        $authService = $this->getAuthService();

        $result = $authService->authenticateById(169111);

        $this->assertTrue($result);
    }

    public function test_authenticateByHash_should_return_false()
    {
        $this->createWishList();

        $authService = $this->getAuthService();

        $result = $authService->authenticateByHash('notExistentHash');

        $this->assertFalse($result);
    }

    public function test_authenticateByHash_should_return_true()
    {
        $this->createWishList();

        $authService = $this->getAuthService();

        $result = $authService->authenticateByHash('customCookieValue');

        $this->assertTrue($result);
    }

    public function test_getBasketList_there_should_be_a_array_of_baskets_hash()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/createOnlyWishlists.sql');
        $this->getConnection()->exec($sql);

        $method = new \ReflectionMethod(WishlistAuthService::class, 'getBasketList');
        $method->setAccessible(true);

        $authService = $this->getAuthService();

        $expectedResult = [
            'hWjNqBeEIQLINAj2hpLu5MiYMg3dkfpwYfKWXRTVFI04zDgQUv',
            'kfWaMGwmmprkiGCh1sb6e4C6O3ks0ULLz1rCCxrqKMQkc6Days',
            '7XUUwHUlt2XaO7vFFYPasmCshj7DnRGTL0xI9NG4VfXPeg9i3b',
            'ha3UrjIHBtRHmBh1XOv7AFel6O0Nsy2wpUKbPvZ8NWuKc17R0N',
            'g6I615V3WVUFWien7Ig3IRVgfLb3lQAUbC13T67btrXrO2Hwez',
            'rv11SlQPVNfY3FJxSLZJgi3OvKMcohbixAa5gYHPsP2cKSzUlk',
        ];

        $result = $method->invoke($authService, 'cookie_value');

        $this->assertEquals($expectedResult, $result);
    }

    public function test_getBasketList_there_should_be_a_array_of_baskets_id()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/createOnlyWishlists.sql');
        $this->getConnection()->exec($sql);

        $method = new \ReflectionMethod(WishlistAuthService::class, 'getBasketList');
        $method->setAccessible(true);

        $authService = $this->getAuthService();

        $expectedResult = [
            '11465',
            '12456',
            '13456',
            '14456',
            '15456',
            '16465',
        ];

        $result = $method->invoke($authService, 'id');

        $this->assertEquals($expectedResult, $result);
    }

    public function test_isPublic_should_be_false()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/createOnlyWishlists.sql');
        $this->getConnection()->exec($sql);

        $authService = $this->getAuthService();
        $result_1 = $authService->isPublic(11465);
        $result_2 = $authService->isPublic(13456);

        $this->assertFalse($result_1);
        $this->assertFalse($result_2);
    }

    public function test_isPublic_should_be_true()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/createOnlyWishlists.sql');
        $this->getConnection()->exec($sql);

        $authService = $this->getAuthService();
        $result_1 = $authService->isPublic(12456);
        $result_2 = $authService->isPublic(14456);

        $this->assertTrue($result_1);
        $this->assertTrue($result_2);
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    /**
     * @return WishlistAuthServiceInterface
     */
    private function getAuthService()
    {
        $this->getContainer()->get('session')->offsetSet('sUserId', 1);

        return new WishlistAuthService(
            $this->getContainer()->get('swag_advanced_cart.dependency_provider'),
            $this->getContainer()->get('dbal_connection')
        );
    }

    private function createWishList()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/createWishlist.sql');
        $this->getConnection()->exec($sql);
    }
}
