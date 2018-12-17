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

namespace SwagBundle\Bundle\SearchBundleDBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class BundleJoinHelper
{
    const BUNDLE_TABLE_JOINED = 'swag_bundle_table';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param QueryBuilder         $query
     * @param ShopContextInterface $context
     */
    public function joinTable(QueryBuilder $query, ShopContextInterface $context)
    {
        if ($query->hasState(self::BUNDLE_TABLE_JOINED)) {
            return;
        }
        $query->addState(self::BUNDLE_TABLE_JOINED);

        $main = $this->connection->createQueryBuilder();
        $main->select('bundle.articleID');
        $main->from('s_articles_bundles', 'bundle');
        $main->innerJoin('bundle', 's_articles', 'product', 'product.id = bundle.articleID');
        $main->innerJoin('bundle', 's_articles_bundles_customergroups', 'customerGroups', 'customerGroups.bundle_id = bundle.id');
        $main->innerJoin('bundle', 's_articles_details', 'productVariants', 'productVariants.articleID = product.id');
        $this->addActiveCondition($main);

        $sub = $this->connection->createQueryBuilder();
        $sub->select('variants.articleID');
        $sub->from('s_articles_bundles_articles', 'main_bundle_products');
        $sub->innerJoin('main_bundle_products', 's_articles_details', 'variants', 'variants.id = main_bundle_products.article_detail_id');
        $sub->innerJoin('main_bundle_products', 's_articles_bundles', 'bundle', 'bundle.id = main_bundle_products.bundle_id');
        $sub->innerJoin('bundle', 's_articles', 'product', 'product.id = bundle.articleID AND bundle.display_global = 1');
        $sub->innerJoin('bundle', 's_articles_bundles_customergroups', 'customerGroups', 'customerGroups.bundle_id = bundle.id');
        $sub->innerJoin('bundle', 's_articles_details', 'productVariants', 'productVariants.articleID = bundle.articleID');

        $this->addActiveCondition($sub);

        $table = implode(' UNION ', [$main->getSQL(), $sub->getSQL()]);

        $query->leftJoin(
            'product',
            '(' . $table . ')',
            'swag_bundles',
            'swag_bundles.articleID = product.id'
        );

        $query->setParameter(':bundleCustomerGroupId', $context->getCurrentCustomerGroup()->getId());
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     */
    private function addActiveCondition(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $query->andWhere('bundle.active = 1');
        $query->andWhere('(productVariants.laststock * productVariants.instock >= productVariants.laststock * productVariants.minpurchase)');
        $query->andWhere('(bundle.valid_from <= NOW() OR bundle.valid_from IS NULL)');
        $query->andWhere('(bundle.valid_to >= NOW() OR bundle.valid_to IS NULL)');
        $query->andWhere('(bundle.max_quantity_enable = 0 OR (bundle.max_quantity_enable = 1 AND bundle.max_quantity > 0))');
        $query->andWhere('customerGroups.customer_group_id = :bundleCustomerGroupId');
    }
}
