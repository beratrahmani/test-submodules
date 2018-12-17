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

namespace SwagLiveShopping\Tests\Functional\Bundle\StoreFrontBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use SwagLiveShopping\Tests\KernelTestCaseTrait;

class ListProductServiceTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;

    public function test_get()
    {
        $listProductService = self::getContainer()->get('shopware_storefront.list_product_service');
        $contextService = self::getContainer()->get('shopware_storefront.context_service');

        $this->installLiveShoppingProduct();
        $product = $listProductService->get('SW10178', $contextService->getShopContext());

        self::assertTrue($product->hasAttribute('live_shopping'));
        self::assertInstanceOf(Attribute::class, $product->getAttribute('live_shopping'));
        self::assertTrue($product->getAttribute('live_shopping')->get('has_live_shopping'));
    }

    private function installLiveShoppingProduct()
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = self::getContainer()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/../../Components/_fixtures/LiveShoppingProduct.sql');
        $databaseConnection->exec($sql);
    }
}
