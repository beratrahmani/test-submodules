<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;

class OrderChangeQueueRepository
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
     * @param string $uuId
     */
    public function setRequestUid(string $uuId)
    {
        $this->connection
            ->executeUpdate('SET @b2b_request_uuid = :uuid;', ['uuid' => $uuId ]);
    }

    /**
     * @return array
     */
    public function fetchAndClearQueueForUuid(): array
    {
        $resultingOrderIds = [];

        $sessionCollate = $this->connection
            ->fetchColumn('SELECT @@SESSION.collation_connection;');

        $this->connection->transactional(function () use (&$resultingOrderIds, $sessionCollate) {
            $result = $this->connection
                ->fetchAll('SELECT * FROM b2b_s_order_change_queue WHERE request_uuid=@b2b_request_uuid COLLATE ' . $sessionCollate . ' GROUP BY s_order_id;');

            $resultingOrderIds = array_map(function (array $queueItem) {
                return (int) $queueItem['s_order_id'];
            }, $result);


            $this->connection
                ->exec('DELETE FROM b2b_s_order_change_queue WHERE request_uuid=@b2b_request_uuid COLLATE ' . $sessionCollate);
        });

        return $resultingOrderIds;
    }

    /**
     * @param int $limit
     * @return int[]
     */
    public function fetchAndClearQueueForCli(int $limit): array
    {
        $resultingOrderIds = [];

        $query = $this->getQuery();

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        $this->connection->transactional(function () use (&$resultingOrderIds, $query) {
            $results = $query->execute()->fetchAll();

            $deleteQuery = $this->connection->createQueryBuilder()
                ->delete('b2b_s_order_change_queue');

            foreach ($results as $result) {
                try {
                    $orderId = $this->getResultingOrderIds($deleteQuery, $result);
                    $resultingOrderIds[$orderId] = $orderId;
                } catch (CanNotUpdateExistingRecordException $e) {
                    // nth
                }
            }
        });

        return $resultingOrderIds;
    }

    /**
     * {@internal}
     * @param QueryBuilder $deleteQuery
     * @param array $result
     * @throws CanNotUpdateExistingRecordException
     * @return int
     */
    protected function getResultingOrderIds(QueryBuilder $deleteQuery, array $result): int
    {
        $deleteQuery->where('s_order_id = :orderId AND updated_at <= :updatedAt');

        if (!$result['request_uuid']) {
            $this->deleteQueryElements($result, $deleteQuery);

            return (int) $result['s_order_id'];
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s.u', $result['updated_at']);

        $minus1Minute = (new \DateTime())->sub(new \DateInterval('P1M'));

        if ($date < $minus1Minute) {
            $this->deleteQueryElements($result, $deleteQuery);

            return (int) $result['s_order_id'];
        }

        $deleteQuery->where('s_order_id = :orderId AND updated_at < :updatedAt');
        $this->deleteQueryElements($result, $deleteQuery);

        throw new CanNotUpdateExistingRecordException('Order "' . $result['s_order_id'] . '" will be updated through http request');
    }

    /**
     * {@internal}
     * @return QueryBuilder
     */
    protected function getQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select('queue.*')
            ->from('b2b_s_order_change_queue', 'queue')
            ->innerJoin(
                'queue',
                '(SELECT MAX(updated_at) AS max_updated_at, s_order_id as grouped_id
                  FROM b2b_s_order_change_queue
                  GROUP BY s_order_id)',
                'queue_grouped',
                's_order_id = grouped_id AND max_updated_at = updated_at'
            )
            ->orderBy('updated_at', 'ASC');
    }

    /**
     * {@internal}
     * @param array $queueData
     * @param QueryBuilder $deleteQuery
     */
    protected function deleteQueryElements(array $queueData, QueryBuilder $deleteQuery)
    {
        $deleteQuery->setParameters([
            'orderId' => $queueData['s_order_id'],
            'updatedAt' => $queueData['updated_at'],
        ])->execute();
    }
}
