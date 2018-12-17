<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Doctrine\DBAL\Query\QueryBuilder;

interface RoleRemoveDependencyValidatorInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $mainTableAlias
     * @param string $alias
     * @param RoleAclGrantContext $grantContext
     */
    public function extendRoleRemoveDependencyQuery(QueryBuilder $queryBuilder, RoleAclGrantContext $grantContext, string $mainTableAlias, string $alias);
}
