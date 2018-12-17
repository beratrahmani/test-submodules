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

namespace SwagBundle\Tests\Functional\Services\Listing;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use SwagBundle\Services\Listing\BundleService;
use SwagBundle\Tests\DatabaseTestCaseTrait;

class BundleServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function test_getListOfBundles_should_return_empty_array_wrong_config()
    {
        /** @var \Shopware_Components_Config $config */
        $config = Shopware()->Container()->get('config');
        $config->offsetSet('SwagBundleShowBundleIcon', false);

        $bundleService = $this->getBundleService($config);

        self::assertEmpty($bundleService->getListOfBundles([]));
    }

    public function test_getListOfBundles_should_return_empty_array_no_bundles()
    {
        /** @var \Shopware_Components_Config $config */
        $config = Shopware()->Container()->get('config');
        $config->offsetSet('SwagBundleShowBundleIcon', true);

        $bundleService = $this->getBundleService($config);

        self::assertEmpty($bundleService->getListOfBundles([]));
    }

    public function test_getListOfBundles_should_return_bundles()
    {
        /** @var \Shopware_Components_Config $config */
        $config = Shopware()->Container()->get('config');
        $config->offsetSet('SwagBundleShowBundleIcon', true);

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/default_bundle.sql'),
            [':bundleId' => 15000]
        );

        $bundleService = $this->getBundleService($config);

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        /** @var ListProductServiceInterface $listProductService */
        $listProductService = Shopware()->Container()->get('shopware_storefront.list_product_service');
        $listProduct = $listProductService->get('SW10178', $contextService->getShopContext());

        $bundles = $bundleService->getListOfBundles([$listProduct]);

        self::assertNotEmpty($bundles);
        self::assertCount(2, $bundles);
    }

    public function test_getListOfBundles_should_return_empty_array_inactive_bundle()
    {
        /** @var \Shopware_Components_Config $config */
        $config = Shopware()->Container()->get('config');
        $config->offsetSet('SwagBundleShowBundleIcon', true);

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/inactive_bundle.sql'),
            [':bundleId' => 15010]
        );

        $bundleService = $this->getBundleService($config);

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        /** @var ListProductServiceInterface $listProductService */
        $listProductService = Shopware()->Container()->get('shopware_storefront.list_product_service');
        $listProduct = $listProductService->get('SW10178', $contextService->getShopContext());

        $bundles = $bundleService->getListOfBundles([$listProduct]);

        self::assertEmpty($bundles);
    }

    public function test_getListOfBundles_should_return_empty_array_bad_config_bundle()
    {
        /** @var \Shopware_Components_Config $config */
        $config = Shopware()->Container()->get('config');
        $config->offsetSet('SwagBundleShowBundleIcon', true);

        // Enable config "max_quantity_enable"
        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/bundle_max_quantity_enabled.sql'),
            [':bundleId' => 15020]
        );

        $bundleService = $this->getBundleService($config);

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');

        /** @var ListProductServiceInterface $listProductService */
        $listProductService = Shopware()->Container()->get('shopware_storefront.list_product_service');
        $listProduct = $listProductService->get('SW10178', $contextService->getShopContext());

        $bundles = $bundleService->getListOfBundles([$listProduct]);

        self::assertEmpty($bundles);
    }

    public function test_createBundleProductQuery_should_have_one_bundle()
    {
        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/active_bundle_last_stock.sql')
        );

        $bundleService = $this->getBundleService();

        $reflectionClass = new \ReflectionClass(BundleService::class);
        $reflectionMethod = $reflectionClass->getMethod('createBundleProductQuery');
        $reflectionMethod->setAccessible(true);

        $queryBuilder = $reflectionMethod->invoke($bundleService);

        $now = new \DateTime();
        $queryBuilder->setParameter(':bundleIds', [1], Connection::PARAM_INT_ARRAY)
            ->setParameter(':now', $now->format('Y-m-d H:i:s'))
            ->setParameter(':customerGroupId', 1);

        $expectedResult = [
            [
                'id' => '1',
                'name' => 'New Bundle',
                'max_quantity_enable' => '0',
                'max_quantity' => '0',
            ],
        ];

        $this->assertArraySubset($expectedResult, $queryBuilder->execute()->fetchAll());
    }

    public function test_createBundleProductQuery_main_laststock_has_no_bundle()
    {
        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/active_bundle_last_stock.sql')
        );

        $bundleService = $this->getBundleService();

        $reflectionClass = new \ReflectionClass(BundleService::class);
        $reflectionMethod = $reflectionClass->getMethod('createBundleProductQuery');
        $reflectionMethod->setAccessible(true);

        Shopware()->Container()->get('dbal_connection')->createQueryBuilder()
            ->update('s_articles_details', 'details')
            ->set('details.laststock', true)
            ->set('details.instock', 0)
            ->where('details.articleID = 272')
            ->execute();

        $queryBuilder = $reflectionMethod->invoke($bundleService);

        $now = new \DateTime();
        $queryBuilder->setParameter(':bundleIds', [1], Connection::PARAM_INT_ARRAY)
            ->setParameter(':now', $now->format('Y-m-d H:i:s'))
            ->setParameter(':customerGroupId', 1);

        $this->assertEmpty($queryBuilder->execute()->fetchAll());
    }

    public function test_createBundleProductQuery_sub_laststock_has_no_bundle()
    {
        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/_fixtures/active_bundle_last_stock.sql')
        );

        $bundleService = $this->getBundleService();

        $reflectionClass = new \ReflectionClass(BundleService::class);
        $reflectionMethod = $reflectionClass->getMethod('createBundleProductQuery');
        $reflectionMethod->setAccessible(true);

        Shopware()->Container()->get('dbal_connection')->createQueryBuilder()
            ->update('s_articles_details', 'details')
            ->set('details.laststock', true)
            ->set('details.instock', 0)
            ->where('details.id = 45')
            ->execute();

        $queryBuilder = $reflectionMethod->invoke($bundleService);

        $now = new \DateTime();
        $queryBuilder->setParameter(':bundleIds', [1], Connection::PARAM_INT_ARRAY)
            ->setParameter(':now', $now->format('Y-m-d H:i:s'))
            ->setParameter(':customerGroupId', 1);

        $this->assertEmpty($queryBuilder->execute()->fetchAll());
    }

    /**
     * @param \Shopware_Components_Config $config
     *
     * @return BundleService
     */
    private function getBundleService($config = null)
    {
        if (!$config) {
            $config = Shopware()->Container()->get('config');
        }

        return new BundleService(
            Shopware()->Container()->get('dbal_connection'),
            $config,
            Shopware()->Container()->get('swag_bundle.customer_group_service')
        );
    }
}
