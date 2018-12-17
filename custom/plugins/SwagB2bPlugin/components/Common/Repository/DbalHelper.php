<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Filter\QueryExtender;
use Throwable;

class DbalHelper
{
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var QueryExtender
     */
    private $queryExtender;

    /**
     * @param Connection $connection
     * @param QueryExtender $queryExtender
     */
    public function __construct(Connection $connection, QueryExtender $queryExtender)
    {
        $this->connection = $connection;
        $this->queryExtender = $queryExtender;
    }

    /**
     * @param callable $inner
     * @throws \RuntimeException
     * @return mixed the callbacks result
     */
    public function transact(callable $inner)
    {
        $this->connection->beginTransaction();
        try {
            $result = $inner();
        } catch (Throwable $t) {
            $this->connection->rollBack();
            throw new \RuntimeException('Transaction failed', 0, $t);
        }

        $this->connection->commit();

        return $result;
    }

    /**
     * @param SearchStruct $searchStruct
     * @param QueryBuilder $query
     */
    public function applySearchStruct(SearchStruct $searchStruct, QueryBuilder $query)
    {
        if ($searchStruct->orderBy) {
            $query->orderBy($searchStruct->orderBy, $searchStruct->orderDirection);
        }

        if (is_int($searchStruct->offset)) {
            $query->setFirstResult($searchStruct->offset);
        }

        if (is_int($searchStruct->limit)) {
            $query->setMaxResults($searchStruct->limit);
        }

        $this->applyFilters($searchStruct, $query);
    }

    /**
     * @param SearchStruct $searchStruct
     * @param QueryBuilder $query
     */
    public function applyFilters(SearchStruct $searchStruct, QueryBuilder $query)
    {
        $this->queryExtender->extendQueryBuilder($searchStruct->filters, $query);
    }
}
