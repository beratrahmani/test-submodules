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

namespace SwagBundle\Services;

use Doctrine\DBAL\Connection;

class ExcludedVariantsService implements ExcludedVariantsServiceInterface
{
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
     * {@inheritdoc}
     */
    public function getExcludedVariantIds($bundleId)
    {
        $mainProductId = $this->getMainProductId($bundleId);

        $productIds = $this->getAssignedProductIds($bundleId);
        $productIds[] = $mainProductId;

        return $this->getExcludedVariantsByProductIds($productIds);
    }

    /**
     * {@inheritdoc}
     */
    public function isVariantInactive($optionId, array $excludedVariants)
    {
        $result = $this->connection->createQueryBuilder()
            ->select(['productVariant.id'])
            ->from('s_articles_details', 'productVariant')
            ->join('productVariant', 's_article_configurator_option_relations', 'relations', 'relations.article_id = productVariant.id')
            ->where('relations.option_id = :optionId')
            ->andWhere('relations.article_id IN (:excludedVariants)')
            ->setParameter('optionId', $optionId)
            ->setParameter('excludedVariants', $excludedVariants, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        return !empty($result);
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getExcludedVariantsByProductIds(array $productIds)
    {
        return $this->connection->createQueryBuilder()
            ->select('id')
            ->from('s_articles_details')
            ->where('articleID IN (:ids)')
            ->andWhere('active = 0')
            ->setParameter('ids', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param int $bundleId
     *
     * @return array
     */
    private function getAssignedProductIds($bundleId)
    {
        return $this->connection->createQueryBuilder()
            ->select('product.id')
            ->from('s_articles', 'product')
            ->join('product', 's_articles_details', 'details', 'details.articleID = product.id')
            ->join('details', 's_articles_bundles_articles', 'bundleArticles', 'bundleArticles.article_detail_id = details.id')
            ->where('bundleArticles.bundle_id = :bundleId')
            ->setParameter('bundleId', $bundleId)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param int $bundleId
     *
     * @return int
     */
    private function getMainProductId($bundleId)
    {
        return (int) $this->connection->createQueryBuilder()
            ->select('articleID')
            ->from('s_articles_bundles')
            ->where('id = :bundleId')
            ->setParameter('bundleId', $bundleId)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }
}
