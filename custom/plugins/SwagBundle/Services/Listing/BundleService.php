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

namespace SwagBundle\Services\Listing;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use SwagBundle\Services\CustomerGroupServiceInterface;
use SwagBundle\Struct\Bundle;

class BundleService implements BundleServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var CustomerGroupServiceInterface
     */
    private $customerGroupService;

    /**
     * @param Connection                    $connection
     * @param \Shopware_Components_Config   $config
     * @param CustomerGroupServiceInterface $customerGroupService
     */
    public function __construct(
        Connection $connection,
        \Shopware_Components_Config $config,
        CustomerGroupServiceInterface $customerGroupService
    ) {
        $this->config = $config;
        $this->connection = $connection;
        $this->customerGroupService = $customerGroupService;
    }

    /**
     * {@inheritdoc}
     */
    public function getListOfBundles($products)
    {
        if ($this->config->get('SwagBundleShowBundleIcon') === false) {
            return [];
        }

        $productIds = $this->getProductIds($products);
        $bundleProductIds = $this->getBundleProductIdsByProductIds($productIds);
        $activeBundles = $this->getActiveBundlesByBundleIds(array_keys($bundleProductIds));

        $bundles = [];
        foreach ($bundleProductIds as $bundleId => $productIds) {
            $productIds = explode(',', $productIds);
            foreach ($productIds as $productId) {
                if (null === $activeBundles[$bundleId]) {
                    continue;
                }

                $bundles[$productId][] = $activeBundles[$bundleId];
            }
        }

        return $bundles;
    }

    /**
     * Returns products' ids
     *
     * @param array $products
     *
     * @return array
     */
    private function getProductIds($products)
    {
        $productIds = array_map(
            function (ListProduct $product) {
                return $product->getId();
            },
            $products
        );

        return $productIds;
    }

    /**
     * Returns all products' ids which are part of a bundle
     * The result is an array with keys -> bundleIds and values -> concatenated productIds
     * array(
     *      $bundleId => "$productId1, $productId2, $productId3)",
     *      $bundleId => "$productId4, $productId5, $productId6)"
     * )
     *
     * @param array $productIds
     *
     * @return array
     */
    private function getBundleProductIdsByProductIds($productIds)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(['bundles.id', "CONCAT_WS(',', bundles.articleID, GROUP_CONCAT(details.articleId))"])
            ->from('s_articles_bundles', 'bundles')
            ->innerJoin('bundles', 's_articles_bundles_articles', 'sub_bundles', 'bundles.id = sub_bundles.bundle_id')
            ->leftJoin(
                'sub_bundles',
                's_articles_details',
                'details',
                'sub_bundles.article_detail_id = details.id AND bundles.display_global'
            )
            ->where('bundles.articleID IN (:productIds)')
            ->orWhere('details.articleId IN (:productIds)')
            ->groupBy('bundles.id')
            ->setParameter(':productIds', $productIds, Connection::PARAM_INT_ARRAY);

        $result = $builder->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
        $result = array_filter($result);

        return $result;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createBundleProductQuery()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder->select(['bundles.id', 'bundles.name', 'bundles.max_quantity_enable', 'bundles.max_quantity'])
            ->from('s_articles_bundles', 'bundles')
            ->innerJoin('bundles', 's_articles', 'product', 'product.id = bundles.articleID')
            ->innerJoin('product', 's_articles_details', 'productDetails', 'product.id = productDetails.articleID')
            ->innerJoin('bundles', 's_articles_bundles_articles', 'bundleArticles', 'bundles.id = bundleArticles.bundle_id')
            ->innerJoin('bundleArticles', 's_articles_details', 'details', 'bundleArticles.article_detail_id = details.id')
            ->innerJoin('bundles', 's_articles_bundles_customergroups', 'bundle_groups', 'bundles.id = bundle_groups.bundle_id')
            ->where('bundles.id IN (:bundleIds)')
            ->andWhere('bundles.active')
            ->andWhere('bundles.valid_to >= :now OR bundles.valid_to IS NULL')
            ->andWhere('bundles.valid_from < :now  OR bundles.valid_from IS NULL')
            ->andWhere('bundle_groups.customer_group_id = :customerGroupId')
            ->andWhere('(productDetails.laststock * productDetails.instock >= productDetails.laststock * productDetails.minpurchase)')
            ->andWhere('(details.laststock * details.instock >= details.laststock * details.minpurchase)');
    }

    /**
     * Returns all active bundles, from the requested listing page
     *
     * @param array $bundleIds
     *
     * @return array
     */
    private function getActiveBundlesByBundleIds($bundleIds)
    {
        $now = new \DateTime();
        $customerGroupId = $this->customerGroupService->getCurrentCustomerGroup()->getId();

        $builder = $this->createBundleProductQuery();
        $builder->setParameter(':bundleIds', $bundleIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':now', $now->format('Y-m-d H:i:s'))
            ->setParameter(':customerGroupId', $customerGroupId);

        $activeBundles = $builder->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $bundles = [];
        foreach ($activeBundles as $activeBundle) {
            if ((bool) $activeBundle['max_quantity_enable'] === true
                && (int) $activeBundle['max_quantity'] <= 0
            ) {
                continue;
            }

            $bundle = new Bundle();
            $bundle->setId((int) $activeBundle['id']);
            $bundle->setName($activeBundle['name']);
            $bundles[$bundle->getId()] = $bundle;
        }

        return $bundles;
    }
}
