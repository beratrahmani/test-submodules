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

namespace SwagLiveShopping\Bundle\SearchBundleDBAL\Facet;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagLiveShopping\Bundle\SearchBundle\Facet\LiveShoppingFacet;
use SwagLiveShopping\Bundle\SearchBundleDBAL\AbstractLiveShoppingHandler;

class LiveShoppingFacetHandler extends AbstractLiveShoppingHandler implements PartialFacetHandlerInterface
{
    /**
     * @var \Enlight_Components_Snippet_Manager
     */
    protected $snippetManager;

    /**
     * @var QueryBuilderFactoryInterface
     */
    protected $queryBuilderFactory;

    /**
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryBuilderFactoryInterface         $queryBuilderFactory
     */
    public function __construct(
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        $this->snippetManager = $snippetManager;
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof LiveShoppingFacet;
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
        $hasLiveShopping = $this->hasLiveShopping($reverted, $context);

        if (!$hasLiveShopping) {
            return null;
        }

        /** @var LiveShoppingFacet $facet */
        $label = $facet->getLabel();
        if ($label === null) {
            $label = $this->snippetManager
                ->getNamespace('frontend/live_shopping/main')
                ->get('liveShoppingFilter');
        }

        return new BooleanFacetResult(
            $facet->getName(),
            'live',
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
    private function hasLiveShopping(Criteria $reverted, ShopContextInterface $context)
    {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);

        $query->select(['liveShopping.id']);
        $query->setFirstResult(0);
        $query->setMaxResults(1);
        $query->andWhere('liveShopping.id IS NOT NULL');

        $this->joinTable($query);

        /* @var \PDOStatement $statement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }
}
