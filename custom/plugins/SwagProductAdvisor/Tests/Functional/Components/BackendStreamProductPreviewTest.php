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

namespace SwagProductAdvisor\Tests\Functional\Components;

use SwagProductAdvisor\Components\Helper\BackendStreamProductPreview;
use SwagProductAdvisor\Structs\BackendSearchResult;
use SwagProductAdvisor\Tests\TestCase;

class BackendStreamProductPreviewTest extends TestCase
{
    public function test_getProductsInStream_get_limited_results()
    {
        $productStream = $this::$helper->createFilteredProductStream([
            'name' => 'Example Advisor Stream',
            'description' => 'Example Product Stream Description Lorem ipsum dolor sit amet',
            'type' => 1,
        ]);

        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestHttp());

        $backendStreamProductPreview = $this->getBackendStreamProductPreview();
        $firstResult = $backendStreamProductPreview->getProductsInStream($productStream->getId(), 2, 0);

        self::assertInstanceOf(BackendSearchResult::class, $firstResult);
        self::assertCount(2, $firstResult->getProductOrderNumbers());

        $secondResult = $backendStreamProductPreview->getProductsInStream($productStream->getId(), 2, 20);

        self::assertInstanceOf(BackendSearchResult::class, $secondResult);
        self::assertCount(2, $secondResult->getProductOrderNumbers());
        self::assertNotEquals($firstResult->getProductOrderNumbers()[0]['id'], $secondResult->getProductOrderNumbers()[0]['id']);
    }

    private function getBackendStreamProductPreview()
    {
        return new BackendStreamProductPreview(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('shopware_storefront.context_service'),
            Shopware()->Container()->get('shopware_product_stream.criteria_factory'),
            Shopware()->Container()->get('shopware_product_stream.repository'),
            Shopware()->Container()->get('shopware_search.product_number_search'),
            Shopware()->Container()->get('swag_product_advisor.default_settings_service'),
            Shopware()->Container()->get('swag_product_advisor.de_hydration')
        );
    }
}
