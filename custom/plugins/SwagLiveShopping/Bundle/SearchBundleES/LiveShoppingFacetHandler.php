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

namespace SwagLiveShopping\Bundle\SearchBundleES;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FilterAggregation;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagLiveShopping\Bundle\SearchBundle\Facet\LiveShoppingFacet;

class LiveShoppingFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @param SnippetManager $snippetManager
     */
    public function __construct(SnippetManager $snippetManager)
    {
        $this->snippetManager = $snippetManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof LiveShoppingFacet;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $filterAggregation = new FilterAggregation('has_live_shopping_filter');
        $filterAggregation->setFilter(FilterProvider::getBoolFilter($context));
        $search->addAggregation($filterAggregation);
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(
        array $elasticResult,
        ProductNumberSearchResult $result,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if (!isset($elasticResult['aggregations'])) {
            return;
        }
        if (!isset($elasticResult['aggregations']['has_live_shopping_filter'])) {
            return;
        }

        $count = $elasticResult['aggregations']['has_live_shopping_filter']['doc_count'];
        if ($count <= 0) {
            return;
        }

        $facet = new BooleanFacetResult(
            'live_shopping',
            'live',
            $criteria->hasCondition('live_shopping'),
            $this->snippetManager->getNamespace('frontend/live_shopping/main')->get('liveShoppingFilter')
        );

        $result->addFacet($facet);
    }
}
