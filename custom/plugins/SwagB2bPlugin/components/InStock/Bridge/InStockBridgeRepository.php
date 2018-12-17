<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Bridge;

use Doctrine\DBAL\Connection;

class InStockBridgeRepository
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
     * @param array $articleDetailIds
     * @return array
     */
    public function getMinPurchases(array $articleDetailIds): array
    {
        $minPurchases = $this->connection->createQueryBuilder()
            ->select('id, minpurchase')
            ->from('s_articles_details')
            ->where('id IN (:id)')
            ->setParameter('id', $articleDetailIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $minPurchases;
    }

    /**
     * @param string $sessionId
     * @return array
     */
    public function getCheckBasketQuantitiesData(string $sessionId)
    {
        return $this->connection->fetchAll(
            'SELECT d.instock, b.quantity, b.ordernumber, d.id as detailId,
                a.laststock, IF(a.active=1, d.active, 0) as active
            FROM s_order_basket b
            LEFT JOIN s_articles_details d
              ON d.ordernumber = b.ordernumber
              AND d.articleID = b.articleID
            LEFT JOIN s_articles a
              ON a.id = d.articleID
            WHERE b.sessionID = :sessionId
              AND b.modus = 0
            GROUP BY b.ordernumber',
            ['sessionId' => $sessionId]
        );
    }
}
