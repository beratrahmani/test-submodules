<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclRepository;

class AclRoleRemoveDependencyValidator implements RoleRemoveDependencyValidatorInterface
{
    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @param AclRepository $aclRepository
     */
    public function __construct(AclRepository $aclRepository)
    {
        $this->aclRepository = $aclRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function extendRoleRemoveDependencyQuery(
        QueryBuilder $queryBuilder,
        RoleAclGrantContext $grantContext,
        string $mainTableAlias,
        string $alias
    ) {
        $tableName = $this->aclRepository->getAssignableTableResolver($grantContext)->getTableName();
        $queryBuilder->leftJoin($mainTableAlias, $tableName, $alias, "{$alias}.entity_id = {$mainTableAlias}.id");
        $queryBuilder->orWhere("{$alias}.id is not null");
    }
}
