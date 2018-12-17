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

namespace SwagAboCommerce\Bundle\SearchBundleES;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FilterAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Snippet_Manager;
use SwagAboCommerce\Bundle\SearchBundle\Facet\AboCommerceFacet;

class AboCommerceESFacetHandler implements AboCommerceESInterface, HandlerInterface, ResultHydratorInterface
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
        return $criteriaPart instanceof AboCommerceFacet;
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
        $aggregation = new ValueCountAggregation('has_abo');
        $aggregation->setField(self::ES_FIELD);

        $filterAgg = new FilterAggregation('has_abo_filter');
        $filterAgg->setFilter(
            new TermQuery(self::ES_FIELD, 1)
        );
        $filterAgg->addAggregation($aggregation);

        $search->addAggregation($filterAgg);
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
        if (!isset($elasticResult['aggregations']['has_abo_filter'])) {
            return;
        }

        $exist = $elasticResult['aggregations']['has_abo_filter']['has_abo']['value'];
        if (!$exist) {
            return;
        }

        $facet = $criteria->getFacet('abo_commerce_product');

        /** @var AboCommerceFacet $facet */
        if ($facet && $facet->getLabel() !== null) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetManager->getNamespace('frontend/abo_commerce/index')->get('ListingTitle');
        }

        $result->addFacet(
            new BooleanFacetResult(
                'abo_commerce_product',
                'abo',
                $criteria->hasCondition('abo_commerce_product'),
                $label
            )
        );
    }
}
