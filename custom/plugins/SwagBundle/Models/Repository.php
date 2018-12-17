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

namespace SwagBundle\Models;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Order\Basket;
use SwagBundle\Components\BundleBasketInterface;

/**
 * Shopware Bundle Model
 * Contains the definition of a single shopware product bundle resource.
 *
 * @category Shopware
 *
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Repository extends ModelRepository
{
    /**
     * The getProductBundlesQuery function is the global interface of the repository to get all bundles
     * for the passed product id.
     * The function calls the internal getProductBundlesQueryBuilder function which generates the query builder
     * with the different sql paths.
     * The getProductBundlesQueryBuilder function can be hooked to modify the query easily over the query builder.
     *
     * @param int      $productId
     * @param array    $filter
     * @param array    $orderBy
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getProductBundlesQuery(
        $productId,
        array $filter = [],
        array $orderBy = [],
        $offset = null,
        $limit = null
    ) {
        $builder = $this->getProductBundlesQueryBuilder($productId, $filter, $orderBy);
        if ($offset !== null) {
            $builder->setFirstResult($offset);
        }
        if ($limit !== null) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * The getProductBundlesQueryBuilder function is a helper function which creates an query builder object
     * to select all bundles for the passed product id.
     * The function returns a query builder object which contains the different sql path to select all product bundles.
     * This function can be hooked to modify the query object easily over the query builder.
     *
     * @param int   $productId
     * @param array $filter
     * @param array $orderBy
     *
     * @return QueryBuilder
     */
    public function getProductBundlesQueryBuilder($productId, array $filter = [], array $orderBy = [])
    {
        /** @var QueryBuilder $builder */
        $builder = $this->createQueryBuilder('bundles')
                        ->where('bundles.articleId = :productId')
                        ->setParameters(['productId' => $productId]);

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * The getBundleQuery function is the global interface of the repository to get bundle data for the passed
     * bundle id.
     * The function calls the internal getBundleQueryBuilder function which generates the query builder
     * with the different sql paths.
     * The getBundleQueryBuilder function can be hooked to modify the query easily over the query builder.
     *
     * @param int $id
     *
     * @return \Doctrine\ORM\Query
     */
    public function getBundleQuery($id)
    {
        $builder = $this->getBundleQueryBuilder($id);

        return $builder->getQuery();
    }

    /**
     * The getBundleQueryBuilder function is a helper function which creates an query builder object
     * to select all bundle data for the passed bundle id.
     * The function returns a query builder object which contains the different sql path to select all bundle data.
     * This function can be hooked to modify the query object easily over the query builder.
     *
     * @param int $id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBundleQueryBuilder($id)
    {
        $builder = $this->getDetailQueryBuilder();

        $builder->leftJoin('bundle.article', 'product');
        $builder->leftJoin('product.tax', 'tax');
        $builder->leftJoin('product.mainDetail', 'mainDetail');
        $builder->leftJoin('bundleProductVariant.article', 'bundleProductVariantProduct');
        $builder->leftJoin('bundleProductVariantProduct.tax', 'bundleProductVariantProductTax');
        $builder->addSelect([
            'product',
            'tax',
            'bundleProductVariantProductTax',
            'PARTIAL mainDetail.{id, number}',
            'PARTIAL bundleProductVariantProduct.{id, name,
             configuratorSetId}',
        ]);

        $builder->where('bundle.id = :id')
                ->setParameters(['id' => $id]);

        $builder->orderBy('bundleProducts.position', 'ASC');

        return $builder;
    }

    /**
     * The getFullListQuery function is the global interface of the repository to get all bundles
     * for the passed with their full data.
     * The function calls the internal getFullListQueryBuilder function which generates the query builder
     * with the different sql paths.
     * The getFullListQueryBuilder function can be hooked to modify the query easily over the query builder.
     *
     * @param array $filter
     * @param array $orderBy
     * @param null  $offset
     * @param null  $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getFullListQuery(array $filter = [], array $orderBy = [], $offset = null, $limit = null)
    {
        $builder = $this->getFullListQueryBuilder($filter, $orderBy);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * The getFullListQueryBuilder function is a helper function which creates an query builder object
     * to select all bundles with their full data.
     * The function returns a query builder object which contains the different sql path to select all bundles.
     * This function can be hooked to modify the query object easily over the query builder.
     *
     * @param array $filter
     * @param array $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFullListQueryBuilder(array $filter = [], array $orderBy = [])
    {
        $builder = $this->getDetailQueryBuilder();

        $builder->leftJoin('bundle.article', 'product')
                ->leftJoin('product.tax', 'tax')
                ->leftJoin('product.mainDetail', 'productMainVariant')
                ->leftJoin('bundleProductVariant.article', 'bundleProductVariantProduct');

        $builder->addSelect([
            'PARTIAL product.{id, name}',
            'PARTIAL productMainVariant.{id, number}',
            'PARTIAL bundleProductVariantProduct.{id, name}',
            'tax',
        ]);

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * @param string $sessionId
     *
     * @return array
     */
    public function getBundleBasketItemsBySessionId($sessionId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->innerJoin('basket.attribute', 'attribute')
            ->where('basket.mode = :mode')
            ->andWhere('basket.sessionId = :sessionId')
            ->andWhere('attribute.bundleId IS NOT NULL')
            ->setParameters(['mode' => BundleBasketInterface::BUNDLE_DISCOUNT_BASKET_MODE, 'sessionId' => $sessionId]);

        return $queryBuilder->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * @param int $bundleId
     *
     * @return array
     */
    public function getLimitedDetails($bundleId)
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        return $queryBuilder->select('bundleStint.id, bundleStint.article_detail_id, details.ordernumber')
            ->from('s_articles_bundles_stint', 'bundleStint')
            ->leftJoin('bundleStint', 's_articles_details', 'details', 'bundleStint.article_detail_id = details.id')
            ->where('bundleStint.bundle_id = :bundleId')
            ->setParameter('bundleId', $bundleId)
            ->execute()
            ->fetchAll();
    }

    /**
     * Internal helper function to create an query builder for the whole bundle data.
     * Used for the backend detail page of a single bundle and on the frontend product detail page.
     *
     * @return QueryBuilder
     */
    protected function getDetailQueryBuilder()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'bundle',
            'bundleCustomerGroups',
            'bundleProducts',
            'bundlePrices',
            'bundleLimitedVariants',
            'bundlePriceCustomerGroup',
            'bundleProductVariant',
            'bundleProductVariantPrices',
            'bundleProductVariantPriceCustomerGroup',
        ]);
        $builder->from(Bundle::class, 'bundle')
                ->leftJoin('bundle.customerGroups', 'bundleCustomerGroups')
                ->leftJoin('bundle.articles', 'bundleProducts')
                ->leftJoin('bundle.prices', 'bundlePrices')
                ->leftJoin('bundlePrices.customerGroup', 'bundlePriceCustomerGroup')
                ->leftJoin('bundle.limitedDetails', 'bundleLimitedVariants')
                ->leftJoin('bundleProducts.articleDetail', 'bundleProductVariant')
                ->leftJoin('bundleProductVariant.prices', 'bundleProductVariantPrices')
                ->leftJoin('bundleProductVariantPrices.customerGroup', 'bundleProductVariantPriceCustomerGroup');

        return $builder;
    }
}
