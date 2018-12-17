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

namespace SwagAboCommerce\Bundle\SearchBundleDBAL;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Snippet_Manager;
use SwagAboCommerce\Bundle\SearchBundle\Facet\AboCommerceFacet;
use SwagAboCommerce\Services\DBALJoinTableServiceInterface;

class AboCommerceFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var DBALJoinTableServiceInterface
     */
    private $joinTableService;

    /**
     * @param DBALJoinTableServiceInterface       $joinTableService
     * @param QueryBuilderFactoryInterface        $queryBuilderFactory
     * @param Shopware_Components_Snippet_Manager $snippetManager
     */
    public function __construct(
        DBALJoinTableServiceInterface $joinTableService,
        QueryBuilderFactoryInterface $queryBuilderFactory,
        Shopware_Components_Snippet_Manager $snippetManager
    ) {
        $this->joinTableService = $joinTableService;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetManager = $snippetManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof AboCommerceFacet;
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
        $data = $this->hasAboProducts($reverted, $context);

        if (empty($data)) {
            return false;
        }
        /** @var AboCommerceFacet $facet */
        $label = $facet->getLabel();
        if ($label === null) {
            $label = $this->snippetManager
                ->getNamespace('frontend/abo_commerce/index')
                ->get('ListingTitle');
        }

        return new BooleanFacetResult(
            $facet->getName(),
            'abo',
            $criteria->hasCondition($facet->getName()),
            $label
        );
    }

    /**
     * @param Criteria             $reverted
     * @param ShopContextInterface $context
     *
     * @return int|false
     */
    private function hasAboProducts(Criteria $reverted, ShopContextInterface $context)
    {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);
        $query->select(['aboProduct.id']);

        $this->joinTableService->joinTable($query);
        $query->andWhere('aboProduct.id IS NOT NULL');

        $query->setFirstResult(0);
        $query->setMaxResults(1);

        $query->groupBy('aboProduct.id');

        /** @var $statement \PDOStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }
}
