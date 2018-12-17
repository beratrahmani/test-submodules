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

namespace SwagTicketSystem\Tests\Functional\Components;

use PHPUnit\Framework\TestCase;
use Shopware\Models\Shop\Shop;
use SwagTicketSystem\Tests\KernelTestCaseTrait;

class DependencyProviderTest extends TestCase
{
    use KernelTestCaseTrait;

    public function test_get_shop()
    {
        $component = self::getContainer()->get('swag_ticket_system.dependency_provider');

        $shop = $component->getShop();

        self::assertInstanceOf(Shop::class, $shop);
        self::assertSame(1, $shop->getId());
    }

    public function test_get_module()
    {
        $component = self::getContainer()->get('swag_ticket_system.dependency_provider');

        $basketModule = $component->getModule('Basket');
        $systemModule = $component->getModule('System');

        self::assertInstanceOf(\sBasket::class, $basketModule);
        self::assertInstanceOf(\sSystem::class, $systemModule);
    }
}
