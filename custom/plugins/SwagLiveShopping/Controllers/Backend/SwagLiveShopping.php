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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use SwagLiveShopping\Models\LiveShopping;

class Shopware_Controllers_Backend_SwagLiveShopping extends Shopware_Controllers_Backend_Application
{
    /**
     * @var string
     */
    protected $model = LiveShopping::class;

    /**
     * @var string
     */
    protected $alias = 'liveShopping';

    /**
     * Overriding to add custom selections to the detail query.
     *
     * {@inheritdoc}
     */
    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        $data = parent::getList($offset, $limit, $sort, $filter, $wholeParams);

        $data = $this->applyProductNames($data);
        $data = $this->applyPrices($data);

        return $data;
    }

    /**
     * Apply the LiveShopingPrices
     *
     * @param array $data
     *
     * @return array
     */
    private function applyPrices(array $data)
    {
        $liveShoppingIds = array_column($data['data'], 'id');
        $productIds = array_column($data['data'], 'articleId');

        $taxes = $this->getTaxes($productIds);
        $prices = $this->getPrices($liveShoppingIds);

        $customerGroupIds = [];
        foreach ($prices as $price) {
            $customerGroupIds = array_merge(array_column($price, 'customer_group_id'), $customerGroupIds);
        }

        $customerGroupNames = $this->getCustomerGroupNames($customerGroupIds);

        foreach ($prices as &$productPrice) {
            $productPrice[0]['customerGroupName'] = $customerGroupNames[$productPrice[0]['customer_group_id']][0]['description'];
        }
        unset($productPrice);

        foreach ($data['data'] as &$liveShoppingProduct) {
            $liveShoppingProduct['prices'] = $prices[$liveShoppingProduct['id']];
            foreach ($liveShoppingProduct['prices'] as &$liveShoppingPrice) {
                $liveShoppingPrice['price'] = $liveShoppingPrice['price'] / 100 * (100 + $taxes[$liveShoppingProduct['articleId']][0]['tax']);
                $liveShoppingPrice['endprice'] = $liveShoppingPrice['endprice'] / 100 * (100 + $taxes[$liveShoppingProduct['articleId']][0]['tax']);
            }
        }

        return $data;
    }

    /**
     * @param array $liveShoppingIds
     *
     * @return array
     */
    private function getPrices(array $liveShoppingIds)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select(['price.id', 'price.*'])
            ->from('s_articles_live_prices', 'price')
            ->where('live_shopping_id IN (:ids)')
            ->setParameter(':ids', $liveShoppingIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getTaxes(array $productIds)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select(['articles.id', 'tax.tax'])
            ->from('s_core_tax', 'tax')
            ->leftJoin('tax', 's_articles', 'articles', 'tax.id = articles.taxID')
            ->where('articles.id IN (:ids)')
            ->setParameter(':ids', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array $customerGroupIds
     *
     * @return array
     */
    private function getCustomerGroupNames(array $customerGroupIds)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select(['id', 'description'])
            ->from('s_core_customergroups')
            ->where('id IN (:ids)')
            ->setParameter(':ids', $customerGroupIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * read and apply the productNames to the data
     *
     * @param array $data
     *
     * @return array
     */
    private function applyProductNames(array $data)
    {
        $productIds = array_column($data['data'], 'articleId');

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $productNames = $queryBuilder->select(['id', 'name'])
            ->from('s_articles')
            ->where('id IN (:ids)')
            ->setParameter(':ids', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);

        foreach ($data['data'] as &$liveShoppingProduct) {
            $liveShoppingProduct['articleName'] = $productNames[$liveShoppingProduct['articleId']][0]['name'];
        }

        return $data;
    }
}
