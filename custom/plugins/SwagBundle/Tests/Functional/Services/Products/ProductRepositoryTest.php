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

namespace SwagBundle\Tests\Functional\Services\Products;

use PHPUnit\Framework\TestCase;
use SwagBundle\Services\Products\ProductRepositoryInterface;

class ProductRepositoryTest extends TestCase
{
    const MAIN_VARIANT_NUMBER = 'SW10002.3';
    const VARIANT_NUMBER = 'SW10002.1';
    const NON_VARIANT_NUMBER = 'SW10003';

    public function test_isNumberFromVariantProduct_should_return_true()
    {
        $productRepository = $this->getProductRepository();

        self::assertTrue($productRepository->isNumberFromVariantProduct(self::MAIN_VARIANT_NUMBER));
        self::assertTrue($productRepository->isNumberFromVariantProduct(self::VARIANT_NUMBER));
    }

    public function test_isNumberFromVariantProduct_should_return_false()
    {
        $productRepository = $this->getProductRepository();

        self::assertFalse($productRepository->isNumberFromVariantProduct(self::NON_VARIANT_NUMBER));
    }

    /**
     * @return ProductRepositoryInterface
     */
    private function getProductRepository()
    {
        return Shopware()->Container()->get('swag_bundle.products.repository');
    }
}
