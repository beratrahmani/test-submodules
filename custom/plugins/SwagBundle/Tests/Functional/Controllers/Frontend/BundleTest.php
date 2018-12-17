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

namespace SwagBundle\Tests\Functional\Controllers\Frontend;

require_once __DIR__ . '/../../../../Controllers/Frontend/Bundle.php';

use SwagBundle\Tests\DatabaseTestCaseTrait;
use SwagBundle\Tests\Functional\BundleControllerTestCase;
use SwagBundle\Tests\Functional\Mocks\BundleFrontendControllerMock;
use SwagBundle\Tests\Functional\Mocks\CustomSortingServiceMock;

class BundleTest extends BundleControllerTestCase
{
    use DatabaseTestCaseTrait;

    public function test_indexAction_should_return_basic_listing_data()
    {
        $view = new \Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $bundleCtl = new BundleFrontendControllerMock(
            $this->Request(),
            $this->Response(),
            Shopware()->Container(),
            $view
        );

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/../_fixtures/default_bundle.sql'),
            [':bundleId' => 13400]
        );

        $bundleCtl->indexAction();
        $totalViewAssign = $view->getAssign();

        self::assertNotCount(0, $totalViewAssign['products']);
        self::assertEquals(2, $totalViewAssign['sNumberArticles']);
        self::assertArrayHasKey('bundle_base', $totalViewAssign['ajaxCountUrlParams']);
    }

    public function test_indexAction_should_add_sortings()
    {
        $view = new \Enlight_View_Default(
            new \Enlight_Template_Manager()
        );

        $bundleCtl = new BundleFrontendControllerMock(
            $this->Request(),
            $this->Response(),
            Shopware()->Container(),
            $view
        );

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            file_get_contents(__DIR__ . '/../_fixtures/default_bundle.sql'),
            [':bundleId' => 13400]
        );

        Shopware()->Container()->set('shopware_storefront.custom_sorting_service', new CustomSortingServiceMock());

        $bundleCtl->indexAction();
        $totalViewAssign = $view->getAssign();

        self::assertArrayHasKey('foo', $totalViewAssign['sortings']);
    }
}
