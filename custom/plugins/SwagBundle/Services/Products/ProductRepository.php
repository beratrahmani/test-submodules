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

namespace SwagBundle\Services\Products;

use Doctrine\DBAL\Connection;

class ProductRepository implements ProductRepositoryInterface
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
    public function isNumberFromVariantProduct($productNumber)
    {
        $dbalQueryBuilder = $this->connection->createQueryBuilder();

        $subQuerySql = $dbalQueryBuilder->select('subDetail.articleID')
            ->from('s_articles_details', 'subDetail')
            ->where('subDetail.ordernumber = :orderNumber')
            ->getSQL();

        $dbalQueryBuilder->resetQueryParts();

        $result = $dbalQueryBuilder->select('1')
            ->from('s_articles_details', 'detail')
            ->where('detail.kind = 2 AND detail.articleID = (' . $subQuerySql . ')')
            ->setParameter(':orderNumber', $productNumber)
            ->execute()
            ->fetch();

        return (bool) $result;
    }
}
