<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * A Context is a valid entity that is either directly (SQL: WHERE) or indirectly (SQL INNER JOIN) a filter to create a ACL-Table subset.
 *
 * These are determined and created through context resolver implementations.
 */
abstract class AclContextResolver
{
    /**
     * @var int
     */
    private static $counter = 0;

    /**
     * @return string
     */
    protected function getCurrentPrefix(): string
    {
        return 'acl_' . self::$counter;
    }

    /**
     * @return string
     */
    protected function getNextPrefix(): string
    {
        self::$counter++;

        return $this->getCurrentPrefix();
    }

    /**
     * @param string $aclTableName
     * @param int $contextId
     * @param QueryBuilder $queryContext
     * @return AclQuery
     */
    abstract public function getQuery(string $aclTableName, int $contextId, QueryBuilder $queryContext): AclQuery;

    /**
     * @param $context
     * @return int
     */
    abstract public function extractId($context): int;

    /**
     * @return bool
     */
    abstract public function isMainContext(): bool;
}
