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

namespace SwagBundle\Tests\Functional\Services\Calculation;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagBundle\Models\Bundle;
use SwagBundle\Models\Repository;
use SwagBundle\Services\Calculation\BundleBasketDiscount;
use SwagBundle\Tests\DatabaseTestCaseTrait;

class UpdateBundleBasketDiscountTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function test_updateBundleBasketDiscount_should_update_discount()
    {
        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');

        $sessionId = 'mySession';
        Shopware()->Session()->offsetSet('sessionId', $sessionId);
        Shopware()->Session()->offsetSet('sUserGroup', 'EK');

        /** @var Repository $bundleRepository */
        $bundleRepository = Shopware()->Container()->get('models')->getRepository(Bundle::class);

        $bundleBasketDiscount = $this->getBundleBasketDiscount();

        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 10200, ':sessionId' => $sessionId, ':basketId' => 120]
        );

        $basketItems = $bundleRepository->getBundleBasketItemsBySessionId($sessionId);

        $bundleBasketDiscount->updateBundleBasketDiscount(
            $basketItems,
            Shopware()->Container()->get('shop')->getCurrency()->getFactor()
        );

        $result = $dbalConnection->fetchAssoc(
            "SELECT * FROM s_order_basket WHERE sessionID=:sessionId AND ordernumber='SWTESTBUNDLE'",
            [':sessionId' => $sessionId]
        );

        self::assertNotEquals(0, $result['netprice']);
    }

    public function test_updateBundleBasketDiscount_should_not_update_discount_wrong_bundle_number()
    {
        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');

        $sessionId = 'mySession';
        Shopware()->Session()->offsetSet('sessionId', $sessionId);
        Shopware()->Session()->offsetSet('sUserGroup', 'EK');

        /** @var Repository $bundleRepository */
        $bundleRepository = Shopware()->Container()->get('models')->getRepository(Bundle::class);

        $bundleBasketDiscount = $this->getBundleBasketDiscount();

        $dbalConnection->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_in_basket.sql'),
            [':bundleId' => 10200, ':sessionId' => $sessionId, ':basketId' => 120]
        );

        /** @var \Shopware\Models\Order\Basket[] $basketItems */
        $basketItems = $bundleRepository->getBundleBasketItemsBySessionId($sessionId);

        $basketItems[0]->setOrderNumber('SWTEST');

        $bundleBasketDiscount->updateBundleBasketDiscount(
            $basketItems,
            Shopware()->Container()->get('shop')->getCurrency()->getFactor()
        );

        $result = $dbalConnection->fetchAssoc(
            "SELECT * FROM s_order_basket WHERE sessionID=:sessionId AND ordernumber='SWTESTBUNDLE'",
            [':sessionId' => $sessionId]
        );

        self::assertEquals(0, $result['netprice']);
    }

    /**
     * @return BundleBasketDiscount
     */
    private function getBundleBasketDiscount()
    {
        return new BundleBasketDiscount(
            Shopware()->Container()->get('models'),
            Shopware()->Container()->get('swag_bundle.dependencies.provider'),
            Shopware()->Container()->get('swag_bundle.full_bundle_service'),
            Shopware()->Container()->get('swag_bundle.customer_group_service'),
            Shopware()->Container()->get('config'),
            Shopware()->Container()->get('swag_bundle.discount.basket_helper'),
            Shopware()->Container()->get('shopware.cart.proportional_tax_calculator'),
            Shopware()->Container()->get('swag_bundle.discount_service')
        );
    }
}
