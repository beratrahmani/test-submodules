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
use SwagBundle\Services\BundleAvailableServiceInterface;
use SwagBundle\Tests\DatabaseTestCaseTrait;
use SwagBundle\Tests\FixtureImportTestCaseTrait;

class BundleAvailableServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use FixtureImportTestCaseTrait;

    const PRODUCT_ID_LAGERKORN = 2;
    const BUNDLE_ID_LIMITED_FOR_VARIANT = 10000;
    const LIMITED_VARIANT_ORDERNUMBER = 'SW10002.1';
    const NOT_LIMITED_VARIANT_ORDERNUMBER = 'SW10002.2';

    const BUNDLE_WITH_INSTOCK_ID = 10001;
    const VARIANT_ORDER_NUMBER = 'SW10002.1';

    const BUNDLE_ID_WITHOUT_LIMITED_VARIANTS = 10004;

    public function test_isBundleAvailable_should_be_available_if_bundle_is_limited_for_variant()
    {
        $bundleAvailableService = $this->getBundleAvailableService();

        $isBundleAvailable = $bundleAvailableService->isBundleAvailable(
            self::PRODUCT_ID_LAGERKORN,
            self::BUNDLE_ID_LIMITED_FOR_VARIANT,
            self::LIMITED_VARIANT_ORDERNUMBER
        );

        $this->assertTrue($isBundleAvailable);
    }

    public function test_isBundleAvailable_should_not_be_available_if_bundle_is_not_limited_for_variant()
    {
        $bundleAvailableService = $this->getBundleAvailableService();

        $isBundleAvailable = $bundleAvailableService->isBundleAvailable(
            self::PRODUCT_ID_LAGERKORN,
            self::BUNDLE_ID_LIMITED_FOR_VARIANT,
            self::NOT_LIMITED_VARIANT_ORDERNUMBER
        );

        $this->assertFalse($isBundleAvailable);
    }

    public function test_isBundleAvailable_should_not_be_available_if_bundle_has_no_instock()
    {
        $bundleAvailableService = $this->getBundleAvailableService();

        $isBundleAvailable = $bundleAvailableService->isBundleAvailable(
            self::PRODUCT_ID_LAGERKORN,
            self::BUNDLE_WITH_INSTOCK_ID,
            self::VARIANT_ORDER_NUMBER
        );

        $this->assertFalse($isBundleAvailable);
    }

    public function test_isBundleAvailable_should_be_available_if_no_limited_variants_were_configured()
    {
        $bundleAvailableService = $this->getBundleAvailableService();

        $isBundleAvailable = $bundleAvailableService->isBundleAvailable(
            237,
            self::BUNDLE_ID_WITHOUT_LIMITED_VARIANTS,
            'SW10228'
        );

        $this->assertTrue($isBundleAvailable);
    }

    /**
     * @return BundleAvailableServiceInterface
     */
    private function getBundleAvailableService()
    {
        return Shopware()->Container()->get('swag_bundle.available_service');
    }
}
