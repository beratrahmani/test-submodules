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

namespace SwagBundle\Tests\Functional\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Order\Basket;
use SwagBundle\Components\BundleBasketInterface;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Models\Bundle;
use SwagBundle\Services\FullBundleServiceInterface;
use SwagBundle\Tests\BundleProviderTrait;
use SwagBundle\Tests\FixtureImportTestCaseTrait;
use SwagBundle\Tests\Functional\TestHelper\BundleData;
use SwagBundle\Tests\Functional\TestHelper\BundleTestDataAdministration;

class FullBundleServiceTest extends TestCase
{
    use BundleProviderTrait;
    use FixtureImportTestCaseTrait;

    /**
     * @var Container
     */
    private $container;

    /**
     * set up the required bundles
     */
    public function setUp()
    {
        $this->container = Shopware()->Container();

        $this->container->get('dbal_connection')->beginTransaction();
        /** @var BundleTestDataAdministration $dataAdministrator */
        $dataAdministrator = $this->container->get('swag_bundle.test_data_administration');
        $dataAdministrator->installBundles();
    }

    /**
     * remove all bundles
     */
    public function tearDown()
    {
        $this->container->get('dbal_connection')->rollBack();
    }

    public function test_bundle_getCalculatedBundle_with_all_products()
    {
        /** @var BundleComponentInterface $bundleComponent */
        $bundleComponent = $this->container->get('swag_bundle.bundle_component');
        $fullBundleService = $this->container->get('swag_bundle.full_bundle_service');
        $bundleData = BundleData::getBundleData();
        $activeBundleArray = $bundleData[0];
        /** @var \SwagBundle\Models\Bundle $activeBundle */
        $activeBundle = $this->container->get('models')->getRepository(Bundle::class)->findOneBy([
            'number' => trim($activeBundleArray['ordernumber'], '"'),
        ]);

        $this->assertNotNull($activeBundle);

        $selection = [];

        foreach ($activeBundle->getArticles() as $bundleProduct) {
            $selection[] = $bundleProduct;
        }

        $bundleComponent->addBundleToBasket($activeBundle->getId(), $selection);
        $activeBundle = $fullBundleService->getCalculatedBundle($activeBundle);

        $this->assertEquals($activeBundle->getDiscount()['display'], '8,28');
    }

    public function test_bundle_getCalculatedBundle_with_selected_products()
    {
        /** @var BundleComponentInterface $bundleService */
        $bundleService = $this->container->get('swag_bundle.bundle_component');
        /** @var FullBundleServiceInterface $fullBundleService */
        $fullBundleService = $this->container->get('swag_bundle.full_bundle_service');

        $bundleData = BundleData::getBundleData();
        $activeBundleArray = $bundleData[0];
        /** @var \SwagBundle\Models\Bundle $activeBundle */
        $activeBundle = $this->container->get('models')->getRepository(Bundle::class)->findOneBy([
            'number' => trim($activeBundleArray['ordernumber'], '"'),
        ]);

        $this->assertNotNull($activeBundle);

        $selection = [];

        foreach ($activeBundle->getArticles() as $bundleProduct) {
            if ($bundleProduct->getArticleDetail()->getNumber() === 'SW10170') {
                continue;
            }

            $selection[] = $bundleProduct;
        }

        $bundleService->addBundleToBasket($activeBundle->getId(), $selection);
        $activeBundle = $fullBundleService->getCalculatedBundle($activeBundle, '', true, $this->getBasketDiscountItem());

        $this->assertEquals($activeBundle->getDiscount()['display'], '4,29');
    }

    public function test_getCalculatedBundle_returns_validation_error_if_customer_group_is_not_allowed()
    {
        $bundle = $this->getBundleWithInvalidCustomerGroups();

        /** @var FullBundleServiceInterface $bundleService */
        $bundleService = $this->container->get('swag_bundle.full_bundle_service');

        $result = $bundleService->getCalculatedBundle($bundle);

        $this->assertEquals(
            ['success' => false, 'bundle' => 'Bundle without customer groups', 'notForCustomerGroup' => true],
            $result
        );
    }

    public function test_getCalculatedBundle_returns_validation_error_if_bundle_is_limited_and_out_of_stock()
    {
        $bundle = $this->getLimitedBundleWithoutInStock();

        /** @var FullBundleServiceInterface $bundleService */
        $bundleService = $this->container->get('swag_bundle.full_bundle_service');

        $result = $bundleService->getCalculatedBundle($bundle);

        $this->assertEquals(
            ['success' => false, 'bundle' => 'Bundle with invalid instock', 'noStock' => true],
            $result
        );
    }

    public function test_getCalculatedBundle_should_calculate_percentage_discount()
    {
        /** @var Bundle $bundle */
        $bundle = $this->container->get('models')->find(Bundle::class, 10005);

        /** @var FullBundleServiceInterface $bundleService */
        $bundleService = $this->container->get('swag_bundle.full_bundle_service');
        $bundleService->getCalculatedBundle($bundle);

        $this->assertEquals(35.979999999999997, $bundle->getTotalPrice()['gross']);
        $this->assertEquals(30.239999999999998, $bundle->getTotalPrice()['net']);
        $this->assertEquals('35,98', $bundle->getTotalPrice()['display']);

        $this->assertEquals(3.597999999999999, $bundle->getDiscount()['gross']);
        $this->assertEquals(3.0240000000000009, $bundle->getDiscount()['net']);
        $this->assertEquals('3,60', $bundle->getDiscount()['display']);

        $this->assertEquals(32.381999999999998, $bundle->getCurrentPrice()->getGrossPrice());
        $this->assertEquals(27.216, $bundle->getCurrentPrice()->getNetPrice());
    }

    public function test_getBundlePrices_should_calculate_absolute_discount()
    {
        /** @var Bundle $bundle */
        $bundle = $this->container->get('models')->find(Bundle::class, 10006);

        /** @var FullBundleServiceInterface $bundleService */
        $bundleService = $this->container->get('swag_bundle.full_bundle_service');
        $bundleService->getCalculatedBundle($bundle);

        $this->assertEquals(35.979999999999997, $bundle->getTotalPrice()['gross']);
        $this->assertEquals(30.239999999999998, $bundle->getTotalPrice()['net']);
        $this->assertEquals('35,98', $bundle->getTotalPrice()['display']);

        $this->assertEquals(24.079999999999998, $bundle->getDiscount()['gross']);
        $this->assertEquals(20.239999999999998, $bundle->getDiscount()['net']);
        $this->assertEquals('24,08', $bundle->getDiscount()['display']);

        $this->assertEquals(11.9, $bundle->getCurrentPrice()->getGrossPrice());
        $this->assertEquals(10, $bundle->getCurrentPrice()->getNetPrice());
    }

    /**
     * @return \Shopware\Models\Order\Basket
     */
    private function getBasketDiscountItem()
    {
        /** @var \Doctrine\ORM\QueryBuilder $builder */
        $builder = $this->container->get('models')->createQueryBuilder();
        $builder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->innerJoin('basket.attribute', 'attribute')
            ->where('basket.mode = :mode')
            ->andWhere('basket.sessionId = :sessionId')
            ->andWhere('attribute.bundleId IS NOT NULL')
            ->setParameters(['mode' => BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE, 'sessionId' => $this->container->get('session')->get('sessionId')]);

        return $builder->getQuery()->getSingleResult();
    }
}
