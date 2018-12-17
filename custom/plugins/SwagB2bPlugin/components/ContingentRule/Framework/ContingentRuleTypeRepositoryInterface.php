<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Doctrine\DBAL\Query\QueryBuilder;

interface ContingentRuleTypeRepositoryInterface
{
    /**
     * @return string
     */
    public function getTableName(): string;

    /**
     * @return string
     */
    public function createSubQuery(): string;

    /**
     * @param QueryBuilder $query
     * @param string $prefix
     */
    public function addSelect(QueryBuilder $query, string $prefix);
}
