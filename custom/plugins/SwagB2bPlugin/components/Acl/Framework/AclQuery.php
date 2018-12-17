<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Data container for ACL subqueries.
 */
class AclQuery
{
    /**
     * @var string
     */
    public $sql;

    /**
     * @var array
     */
    public $params;

    /**
     * @param string $sql
     * @param array $params
     * @return AclQuery
     */
    public function fromPrimitives(string $sql, array $params): self
    {
        $this->sql = $sql;
        $this->params = $params;

        return $this;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return AclQuery
     */
    public function fromQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $this->sql = $queryBuilder->getSQL();
        $this->params = $queryBuilder->getParameters();

        return $this;
    }
}
