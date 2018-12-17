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

namespace SwagBundle\Bundle\SearchBundleES\FacetHandler;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FilterAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Snippet_Manager;
use SwagBundle\Bundle\SearchBundle\Facet\BundleFacet;

class BundleFacetHandlerEs implements HandlerInterface, ResultHydratorInterface
{
    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @param Shopware_Components_Snippet_Manager $snippetManager
     */
    public function __construct(Shopware_Components_Snippet_Manager $snippetManager)
    {
        $this->snippetManager = $snippetManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof BundleFacet;
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
        $now = new \DateTime();
        $field = 'attributes.swag_bundle.group_' . $context->getCurrentCustomerGroup()->getId();

        $query = new BoolQuery();
        $query->add(
            new RangeQuery(
                $field . '.valid_from',
                ['lte' => $now->format('Y-m-d H:i:s')]
            )
        );
        $query->add(
            new RangeQuery(
                $field . '.valid_to',
                ['gte' => $now->format('Y-m-d H:i:s')]
            )
        );

        $search->addAggregation(new FilterAggregation('swag_bundle', $query));
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
        if (!isset($elasticResult['aggregations']['swag_bundle'])) {
            return;
        }

        $data = $elasticResult['aggregations']['swag_bundle'];
        if ((int) $data['doc_count'] === 0) {
            return;
        }

        $facetName = 'bundle';
        /** @var BundleFacet $facet */
        $facet = $criteria->getFacet($facetName);
        $label = null;

        if ($facet !== null) {
            $label = $facet->getLabel();
        }

        if ($label === null) {
            $label = $this->snippetManager
                ->getNamespace('frontend/listing/bundle')
                ->get('FacetName');
        }

        $result->addFacet(
            new BooleanFacetResult(
                $facetName,
                'bundle',
                $criteria->hasCondition($facetName),
                $label
            )
        );
    }
}
