<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * A default implementation for main contexts.
 */
abstract class AclContextResolverMain extends AclContextResolver
{
    /**
     * @param string $aclTableName
     * @param int $contextId
     * @param QueryBuilder $queryBuilder
     * @return AclQuery
     */
    public function getQuery(string $aclTableName, int $contextId, QueryBuilder $queryBuilder): AclQuery
    {
        $mainPrefix = $this->getNextPrefix();

        $queryBuilder
            ->select($mainPrefix . '.*')
            ->from($aclTableName, $mainPrefix)
            ->where($mainPrefix . '.entity_id = :p_' . $mainPrefix)
            ->setParameter('p_' . $mainPrefix, $contextId);

        return (new AclQuery())->fromQueryBuilder($queryBuilder);
    }

    /**
     * @return bool
     */
    public function isMainContext(): bool
    {
        return true;
    }
}
