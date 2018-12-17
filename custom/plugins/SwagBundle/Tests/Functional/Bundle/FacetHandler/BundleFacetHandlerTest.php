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

namespace SwagBundle\Tests\Functional\Bundle\FacetHandler;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use SwagBundle\Bundle\SearchBundle\Facet\BundleFacet;
use SwagBundle\Bundle\SearchBundleDBAL\FacetHandler\BundleFacetHandler;
use SwagBundle\Tests\DatabaseTestCaseTrait;

class BundleFacetHandlerTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function test_generatePartialFacet_should_return_booleanFacet()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/install_variant_bundle.sql');
        Shopware()->Container()->get('dbal_connection')->executeQuery($sql);

        $facet = new BundleFacet('FacetLabel');
        $revertedCriteria = new Criteria();
        $criteria = new Criteria();
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1, 1, 'EK');

        $facetHandler = $this->createFacetHandler();

        $result = $facetHandler->generatePartialFacet($facet, $revertedCriteria, $criteria, $context);

        $this->assertInstanceOf(BooleanFacetResult::class, $result);
    }

    public function test_generatePartialFacet_should_return_NULL()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/install_variant_bundle.sql');
        Shopware()->Container()->get('dbal_connection')->executeQuery($sql);

        $sql = 'UPDATE s_articles_details SET instock = 0';
        Shopware()->Container()->get('dbal_connection')->executeQuery($sql);

        $facet = new BundleFacet('FacetLabel');
        $revertedCriteria = new Criteria();
        $criteria = new Criteria();
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1, 1, 'EK');

        $facetHandler = $this->createFacetHandler();

        $result = $facetHandler->generatePartialFacet($facet, $revertedCriteria, $criteria, $context);

        $this->assertNull($result);
    }

    /**
     * @return BundleFacetHandler
     */
    private function createFacetHandler()
    {
        $queryBuilderFactory = Shopware()->Container()->get('shopware_searchdbal.dbal_query_builder_factory');
        $bundleJoinHelper = Shopware()->Container()->get('swag_bundle.search_bundle_dbal.join_helper');
        $snippetManager = Shopware()->Container()->get('snippets');

        return new BundleFacetHandler(
            $queryBuilderFactory,
            $bundleJoinHelper,
            $snippetManager
        );
    }
}
