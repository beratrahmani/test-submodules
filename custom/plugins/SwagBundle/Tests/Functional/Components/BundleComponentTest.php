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

namespace SwagBundle\Tests\Functional\Components;

use PHPUnit\Framework\TestCase;
use SwagBundle\Components\BundleComponentInterface;
use SwagBundle\Tests\DatabaseTestCaseTrait;
use SwagBundle\Tests\FixtureImportTestCaseTrait;

class BundleComponentTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use FixtureImportTestCaseTrait;

    const PRODUCT_ID_WITH_BUNDLE = 2;
    const PRODUCT_NUMBER_WITH_BUNDLE = 'SW10002.1';

    const PRODUCT_ID_WITHOUT_BUNDLE = 272;
    const PRODUCT_NUMBER_WITHOUT_BUNDLE = 'SW10239';

    const PRODUCT_ID_WITH_MULTIPLE_BUNDLES = 218;
    const PRODUCT_NUMBER_WITH_MULTIPLE_BUNDLES = 'SW10216.3';

    public function test_getBundlesForDetailPage_should_return_1_bundle()
    {
        $bundleComponent = $this->getBundleComponent();

        $bundlesForDetailPage = $bundleComponent->getBundlesForDetailPage(
            self::PRODUCT_ID_WITH_BUNDLE,
            self::PRODUCT_NUMBER_WITH_BUNDLE
        );

        $this->assertCount(1, $bundlesForDetailPage);
        $this->assertEquals('Bundle is limited to variant', $bundlesForDetailPage[0]['name']);
    }

    public function test_getBundlesForDetailPage_should_return_false_if_product_has_no_bundle()
    {
        $bundleComponent = $this->getBundleComponent();

        $result = $bundleComponent->getBundlesForDetailPage(
            self::PRODUCT_ID_WITHOUT_BUNDLE,
            self::PRODUCT_NUMBER_WITHOUT_BUNDLE
        );

        $this->assertFalse($result);
    }

    public function test_getBundlesForDetailPage_should_return_all_configured_bundles()
    {
        $bundleComponent = $this->getBundleComponent();

        $bundlesForDetailPage = $bundleComponent->getBundlesForDetailPage(
            self::PRODUCT_ID_WITH_MULTIPLE_BUNDLES,
            self::PRODUCT_NUMBER_WITH_MULTIPLE_BUNDLES
        );

        $this->assertCount(2, $bundlesForDetailPage);
    }

    /**
     * @return BundleComponentInterface
     */
    private function getBundleComponent()
    {
        return Shopware()->Container()->get('swag_bundle.bundle_component');
    }
}
