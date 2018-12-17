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

use SwagAdvancedCart\Components\StaticSavedItemUpdater;
use SwagAdvancedCart\Services\BasketUtils;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class StaticSavedItemUpdaterTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_issetRequirements_should_be_false()
    {
        $this->assertFalse(StaticSavedItemUpdater::issetRequirements());
    }

    public function test_issetRequirements_should_be_True()
    {
        $this->setRequirements();
        $this->asserttrue(StaticSavedItemUpdater::issetRequirements());
    }

    public function test_updateSavedBasketItemQuantity()
    {
        $this->createWishList();
        $this->setRequirements();

        StaticSavedItemUpdater::updateSavedBasketItemQuantity(1177111, 12);

        $quantity = $this->getConnection()->createQueryBuilder()
            ->select('quantity')
            ->from('s_order_basket_saved_items')
            ->where('id = 323111')
            ->execute()
            ->fetchColumn();

        $this->assertSame('12', $quantity);
    }

    public function test_isArticleUpdated()
    {
        $this->createWishList();
        $this->setRequirements();

        StaticSavedItemUpdater::updateSavedBasketItemQuantity(1180111, 12);

        $this->assertTrue(StaticSavedItemUpdater::isProductUpdated(1180111));
    }

    public function test_findOrderNumberByBasketId()
    {
        $this->createWishList();
        $this->setRequirements();

        $this->assertSame('SW10036', StaticSavedItemUpdater::findOrderNumberByBasketId(1179111));
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    private function createWishList()
    {
        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/createWishlist.sql'));
    }

    private function setRequirements()
    {
        $basketUtils = new BasketUtils(
            $this->getConnection(),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('swag_advanced_cart.dependency_provider')
        );

        StaticSavedItemUpdater::setRequirements($basketUtils, 1);
    }
}
