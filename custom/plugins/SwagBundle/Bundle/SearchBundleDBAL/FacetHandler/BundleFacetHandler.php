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

namespace SwagBundle\Bundle\SearchBundleDBAL\FacetHandler;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagBundle\Bundle\SearchBundle\Facet\BundleFacet;
use SwagBundle\Bundle\SearchBundleDBAL\BundleJoinHelper;

class BundleFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var BundleJoinHelper
     */
    private $bundleJoinHelper;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @param QueryBuilderFactoryInterface         $queryBuilderFactory
     * @param BundleJoinHelper                     $bundleJoinHelper
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     */
    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        BundleJoinHelper $bundleJoinHelper,
        \Shopware_Components_Snippet_Manager $snippetManager
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->bundleJoinHelper = $bundleJoinHelper;
        $this->snippetManager = $snippetManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof BundleFacet;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);

        $query->select(['product.id']);
        $query->setMaxResults(1);

        $this->bundleJoinHelper->joinTable($query, $context);

        $query->andWhere('swag_bundles.articleID IS NOT NULL');

        /** @var \PDOStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($data)) {
            return null;
        }

        /** @var BundleFacet $facet */
        $label = $facet->getLabel();

        if ($label === null) {
            $label = $this->snippetManager
                ->getNamespace('frontend/listing/bundle')
                ->get('FacetName');
        }

        return new BooleanFacetResult(
            $facet->getName(),
            'bundle',
            $criteria->hasCondition($facet->getName()),
            $label
        );
    }
}
