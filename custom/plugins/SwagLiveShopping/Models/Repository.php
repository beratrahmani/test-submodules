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

namespace SwagLiveShopping\Models;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Detail;
use Shopware\Models\Attribute\OrderBasket;
use Shopware\Models\Customer\Group;
use Shopware\Models\Order\Basket;
use Shopware\Models\Shop\Shop;

/**
 * Repository of the SwagLiveShopping plugin.
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class Repository extends ModelRepository
{
    /**
     * Listing query builder.
     *
     * Returns an query builder object for a LiveShopping record list.
     * The listing query builder of this repository is used for the backend listing module
     * of this plugin or for the frontend listing.
     *
     * @param int   $productId
     * @param array $filter
     * @param array $sort
     * @param int   $offset
     * @param int   $limit
     *
     * @return QueryBuilder
     */
    public function getListQueryBuilder($productId, $filter, $sort, $offset = null, $limit = null)
    {
        /* @var QueryBuilder $builder */
        $builder = $this->createQueryBuilder('LiveShopping');

        $builder->andWhere('LiveShopping.articleId = :productId');
        $builder->setParameter('productId', $productId);

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        if ($limit !== null && $offset !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * @param array        $filter
     * @param string|array $sort
     * @param int|null     $offset
     * @param int|null     $limit
     *
     * @return QueryBuilder
     */
    public function getFullListQueryBuilder(array $filter = [], $sort, $offset = null, $limit = null)
    {
        $expression = new Expr();

        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['liveShopping', 'prices', 'customerGroup'])
            ->from(LiveShopping::class, 'liveShopping')
            ->leftJoin('liveShopping.prices', 'prices')
            ->leftJoin('prices.customerGroup', 'customerGroup')
            ->leftJoin('liveShopping.shops', 'shops')
            ->leftJoin('liveShopping.customerGroups', 'liveShoppingCustomerGroups')
            ->leftJoin('liveShopping.limitedVariants', 'limitedVariants')
            ->leftJoin('liveShopping.article', 'product')
            ->leftJoin('product.tax', 'tax');

        $builder->addSelect(['limitedVariants', 'product', 'tax']);

        //create a filter for the bundle quantity limitation.
        //filters all bundles which limited and have no more stock left.
        $limitFilter = $expression->orX(
            $expression->eq('liveShopping.limited', 0),
            $expression->andX(
                $expression->eq('liveShopping.limited', 1),
                $expression->gt('liveShopping.quantity', 0)
            )
        );

        //adds the created filters.
        $builder->addFilter([
            $limitFilter,
        ]);

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        if ($limit !== null && $offset !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Detail query builder.
     *
     * Returns an query builder object which selects a single LiveShopping record with
     * the minimum stack of the LiveShopping associations to prevent an stack overflow
     * in the query.
     * Info: Don't join N:M associations, this would blast the query result and doctrine
     * has to iterate millions of records and the function runs into a timeout.
     *
     * @param int $id
     *
     * @return QueryBuilder
     */
    public function getDetailQueryBuilder($id)
    {
        /* @var  QueryBuilder $builder */
        $builder = $this->createQueryBuilder('LiveShopping');

        $builder->leftJoin('LiveShopping.limitedVariants', 'limitedVariants')
            ->leftJoin('LiveShopping.prices', 'prices')
            ->leftJoin('LiveShopping.article', 'product')
            ->leftJoin('product.tax', 'tax')
            ->leftJoin('prices.customerGroup', 'customerGroup');

        $builder->addSelect(['prices', 'limitedVariants', 'customerGroup', 'product', 'tax']);

        $builder->andWhere('LiveShopping.id = :id')
            ->setParameter('id', $id);

        return $builder;
    }

    /**
     * Customer groups query builder.
     *
     * Returns an query builder object which selects the customer groups association of a single LiveShopping record.
     *
     * Info: Don't join N:M associations, this would blast the query result and doctrine
     * has to iterate millions of records and the function runs into a timeout.
     *
     * @param int $id
     *
     * @return QueryBuilder
     */
    public function getCustomerGroupsQueryBuilder($id)
    {
        $builder = $this->createQueryBuilder('liveShopping');
        $builder->leftJoin('liveShopping.customerGroups', 'customerGroups');
        $builder->addSelect(['customerGroups']);
        $builder->where('liveShopping.id = :id');
        $builder->setParameter('id', $id);

        return $builder;
    }

    /**
     * Shops query builder.
     *
     * Returns an query builder object which selects the shops association of a single LiveShopping record.
     *
     * Info: Don't join N:M associations, this would blast the query result and doctrine
     * has to iterate millions of records and the function runs into a timeout.
     *
     * @param int $id
     *
     * @return QueryBuilder
     */
    public function getShopsQueryBuilder($id)
    {
        $builder = $this->createQueryBuilder('liveShopping');
        $builder->leftJoin('liveShopping.shops', 'shops');
        $builder->addSelect(['shops']);
        $builder->andWhere('liveShopping.id = :id');
        $builder->setParameter('id', $id);

        return $builder;
    }

    /**
     * Active query builder.
     *
     * Creates an query builder objects which searches an active and valid
     * live shopping product for the passed product id.
     * This query is used for the shop frontend to find live shopping
     * products which has to be displayed.
     *
     * @param int         $productId
     * @param Detail|null $variant
     * @param Group|null  $customerGroup
     * @param Shop|null   $shop
     *
     * @return QueryBuilder
     */
    public function getActiveLiveShoppingForProductQueryBuilder(
        $productId,
        $variant = null,
        $customerGroup = null,
        $shop = null
    ) {
        $builder = $this->getActiveLiveShoppingQueryBuilder($customerGroup, $shop);

        ///creates a filter for the passed product id.
        $builder->andWhere('liveShopping.articleId = :productId');
        $builder->setParameter('productId', (int) $productId);

        //Search if current product is variant and there is limitation for it
        if ($variant && (int) $variant->getKind() === 2) {
            $builder->leftJoin('liveShopping.limitedVariants', 'variants');
            $builder->andWhere('variants.id = :detailId');
            $builder->setParameter('detailId', (int) $variant->getId());
        }

        return $builder;
    }

    /**
     * Active live shopping query builder.
     *
     * This function returns the query builder for all active live shopping products
     * The function is used to get all live-shopping id's in the responsive-template.
     *
     * @param Group $customerGroup
     * @param null  $shop
     *
     * @return QueryBuilder
     */
    public function getLiveShoppingIdQueryBuilder($customerGroup = null, $shop = null)
    {
        //create helper objects
        $now = new \DateTime();
        $expression = new Expr();

        /* @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('PARTIAL liveShopping.{id}')
            ->from(LiveShopping::class, 'liveShopping')
            ->leftJoin('liveShopping.prices', 'prices')
            ->leftJoin('prices.customerGroup', 'customerGroup')
            ->leftJoin('liveShopping.shops', 'shops')
            ->leftJoin('liveShopping.customerGroups', 'liveShoppingCustomerGroups');

        //creates a filter for the active flag
        $activeFilter = $expression->eq('liveShopping.active', true);

        //create a filter for the bundle quantity limitation.
        //filters all bundles which limited and have no more stock left.
        $limitFilter = $expression->orX(
            $expression->eq('liveShopping.limited', 0),
            $expression->andX(
                $expression->eq('liveShopping.limited', 1),
                $expression->gt('liveShopping.quantity', 0)
            )
        );

        //create valid to filter for the time control.
        //create valid from filter for the time control.
        $builder->andWhere('liveShopping.validTo >= :validTo');
        $builder->andWhere('liveShopping.validFrom <= :validFrom');
        $builder->setParameter('validTo', $now->format('Y-m-d H:i:00'));
        $builder->setParameter('validFrom', $now->format('Y-m-d H:i:00'));

        if ($customerGroup instanceof Group) {
            //create a filter for the customer group limitation.
            //used to select only bundles which should be displayed for the passed customer group id.
            $builder->andWhere('liveShoppingCustomerGroups.id = :customerGroupId');
            $builder->andWhere('customerGroup.id = :customerGroupId');
            $builder->setParameter('customerGroupId', $customerGroup->getId());
        }

        if ($shop instanceof Shop) {
            //create a filter for the shop limitation.
            //used to select only bundles which should be displayed for the passed shop id.
            $builder->andWhere('shops.id = :shopId');
            $builder->setParameter('shopId', $shop->getId());
        }

        //adds the created filters.
        $builder->addFilter(
            [
                $activeFilter,
                $limitFilter,
            ]
        );

        return $builder;
    }

    /**
     * Active query builder.
     *
     * Creates an query builder objects which returns the live shopping product
     * data for the passed live shopping id.
     * This query is used for the shop frontend to refresh the live shopping data
     * of a displayed live shopping product on the product detail page.
     *
     * @param int        $liveShoppingId
     * @param Group|null $customerGroup
     * @param Shop|null  $shop
     *
     * @return QueryBuilder
     */
    public function getActiveLiveShoppingByIdQueryBuilder($liveShoppingId, $customerGroup = null, $shop = null)
    {
        $builder = $this->getActiveLiveShoppingQueryBuilder($customerGroup, $shop);

        $builder->andWhere('liveShopping.id = :id');
        $builder->setParameter('id', (int) $liveShoppingId);

        return $builder;
    }

    /**
     * Active live shopping query builder.
     *
     * This function returns the query builder for all active live shopping products
     * The function is used for the getActiveLiveShoppingForProductQueryBuilder for the emotion-template.
     *
     * @param Group|null $customerGroup
     * @param Shop|null  $shop
     *
     * @return QueryBuilder
     */
    public function getActiveLiveShoppingQueryBuilder(Group $customerGroup = null, $shop = null)
    {
        //create helper objects
        $now = new \DateTime();
        $expression = new Expr();

        /* @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['liveShopping', 'prices', 'customerGroup'])
            ->from(LiveShopping::class, 'liveShopping')
            ->leftJoin('liveShopping.prices', 'prices')
            ->leftJoin('prices.customerGroup', 'customerGroup')
            ->leftJoin('liveShopping.shops', 'shops')
            ->leftJoin('liveShopping.customerGroups', 'liveShoppingCustomerGroups');

        //creates a filter for the active flag
        $activeFilter = $expression->eq('liveShopping.active', true);

        //create a filter for the bundle quantity limitation.
        //filters all bundles which limited and have no more stock left.
        $limitFilter = $expression->orX(
            $expression->eq('liveShopping.limited', 0),
            $expression->andX(
                $expression->eq('liveShopping.limited', 1),
                $expression->gt('liveShopping.quantity', 0)
            )
        );

        //create valid to filter for the time control.
        //create valid from filter for the time control.
        $builder->andWhere('liveShopping.validTo >= :validTo');
        $builder->andWhere('liveShopping.validFrom <= :validFrom');
        $builder->setParameter('validTo', $now->format('Y-m-d H:i:00'));
        $builder->setParameter('validFrom', $now->format('Y-m-d H:i:00'));

        if ($customerGroup instanceof Group) {
            //create a filter for the customer group limitation.
            //used to select only bundles which should be displayed for the passed customer group id.
            $builder->andWhere('liveShoppingCustomerGroups.id = :customerGroupId');
            $builder->andWhere('customerGroup.id = :customerGroupId');
            $builder->setParameter('customerGroupId', $customerGroup->getId());
        }

        if ($shop instanceof Shop) {
            //create a filter for the shop limitation.
            //used to select only bundles which should be displayed for the passed shop id.
            $builder->andWhere('shops.id = :shopId');
            $builder->setParameter('shopId', $shop->getId());
        }

        //adds the created filters.
        $builder->addFilter([
            $activeFilter,
            $limitFilter,
        ]);

        return $builder;
    }

    /**
     * Basket live shoppings.
     *
     * This query builder is used to get the last added live shopping product in the basket.
     * The function query builder is used to return the product data in the afterAddArticle hook.
     *
     * @param string $sessionId
     *
     * @return DoctrineQueryBuilder
     */
    public function getLastBasketLiveShoppingQueryBuilder($sessionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['basket', 'attribute'])
            ->from(Basket::class, 'basket')
            ->innerJoin('basket.attribute', 'attribute')
            ->where('basket.sessionId = :sessionId')
            ->andWhere('attribute.swagLiveShoppingId IS NOT NULL')
            ->setParameter('sessionId', $sessionId)
            ->orderBy('basket.id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        return $builder;
    }

    /**
     * Basket live shoppings.
     *
     * This query builder is used to get all live shopping products of the basket
     * for the passed session id. Used to validate and update basket live shopping products.
     *
     * @param string $sessionId
     *
     * @return DoctrineQueryBuilder
     */
    public function getBasketAttributesWithLiveShoppingFlagQueryBuilder($sessionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['attribute'])
            ->from(OrderBasket::class, 'attribute')
            ->innerJoin('attribute.orderBasket', 'basket')
            ->where('basket.sessionId = :sessionId')
            ->andWhere('attribute.swagLiveShoppingId IS NOT NULL')
            ->setParameter('sessionId', $sessionId);

        return $builder;
    }

    /**
     * Backend variant listing query builder.
     *
     * This query builder is used for the "limited variants" tab panel in the live shopping
     * backend extension in the product module. The query builder selects an limited offset
     * of product variants for the passed product id.
     *
     * @param int $productId
     * @param int $offset
     * @param int $limit
     *
     * @return DoctrineQueryBuilder
     */
    public function getProductVariantsQueryBuilder($productId, $offset, $limit)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['variant'])
            ->from(Detail::class, 'variant')
            ->where('variant.articleId = :productId')
            ->setParameter('productId', $productId)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder;
    }

    /**
     * Creates an query builder objects which returns the live shopping product
     * data for the passed main category id.
     * This query is only used for the newsletter plugin
     *
     * @param int $categoryId
     *
     * @return QueryBuilder
     */
    public function getActiveLiveShoppingByMainCategory($categoryId)
    {
        $builder = $this->getActiveLiveShoppingQueryBuilder();
        $builder->leftJoin('liveShopping.article', 'product');
        $builder->leftJoin('product.allCategories', 'allCategories');
        $builder->andWhere('allCategories.id = :categoryId');
        $builder->setParameter('categoryId', $categoryId);

        return $builder;
    }

    /**
     * This query builder is used for newsletter live shopping products widget search
     * for displaying only live shopping products.
     *
     * @param array $filter
     *
     * @return DoctrineQueryBuilder
     */
    public function getLiveShoppingProductBuilder($filter)
    {
        $now = new \DateTime();

        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['product.id as articleId', 'product.name'])
            ->from(LiveShopping::class, 'liveShopping')
            ->leftJoin('liveShopping.article', 'product')
            ->leftJoin('product.mainDetail', 'detail');
        $builder->expr()->like('liveShopping.number', 'SW12');

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        //create a filter for the bundle quantity limitation.
        //filters all bundles which limited and have no more stock left.
        $expression = new Expr();
        $limitFilter = $expression->orX(
            $expression->eq('liveShopping.limited', 0),
            $expression->andX(
                $expression->eq('liveShopping.limited', 1),
                $expression->gt('liveShopping.quantity', 0)
            )
        );

        $builder->addFilter([
            $limitFilter,
        ]);

        //create valid to filter for the time control.
        //create valid from filter for the time control.
        $builder->andWhere('liveShopping.validTo >= :validTo');
        $builder->andWhere('liveShopping.validFrom <= :validFrom');
        $builder->setParameter('validTo', $now->format('Y-m-d H:i:00'));
        $builder->setParameter('validFrom', $now->format('Y-m-d H:i:00'));

        return $builder;
    }
}
