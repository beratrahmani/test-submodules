<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory as ShopwareQueryBuilderFactory;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class QueryBuilderFactory extends ShopwareQueryBuilderFactory
{
    /**
     * @var ShopwareQueryBuilderFactory
     */
    private $decorated;

    public function __construct(
    ) {
        $this->decorated = new ShopwareQueryBuilderFactory(... func_get_args());
        parent::__construct(... func_get_args());
    }

    /**
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return QueryBuilder
     */
    public function createQuery(Criteria $criteria, ShopContextInterface $context)
    {
        if ($criteria->hasBaseCondition(VariantCondition::NAME)) {
            $query = $this->createQueryBuilder();

            $query->from('s_articles', 'product');

            $query->innerJoin(
                'product',
                's_articles_details',
                'variant',
                'variant.articleID = product.id
                 AND variant.active = 1
                 AND product.active = 1'
            );

            $query->innerJoin(
                'variant',
                's_articles_attributes',
                'productAttribute',
                'productAttribute.articledetailsID = variant.id'
            );

            $addConditions = function (ShopwareQueryBuilderFactory $queryBuilderFactory) use ($criteria, $query, $context) {
                $queryBuilderFactory->addConditions($criteria, $query, $context);

                return $query;
            };

            $addConditions = \Closure::bind($addConditions, null, $this->decorated);

            return $addConditions($this->decorated);
        }

        return parent::createQuery($criteria, $context);
    }
}
