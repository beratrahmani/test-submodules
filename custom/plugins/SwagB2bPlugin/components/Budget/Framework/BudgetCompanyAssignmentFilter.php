<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Company\Framework\CompanyAssignmentFilter;
use Shopware\B2B\Company\Framework\CompanyFilterStruct;

class BudgetCompanyAssignmentFilter implements CompanyAssignmentFilter
{
    /**
     * @param CompanyFilterStruct $filterStruct
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilter(CompanyFilterStruct $filterStruct, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->innerJoin(
                BudgetRepository::TABLE_ALIAS,
                'b2b_acl_role_budget',
                'RoleBudgetBudget',
                'RoleBudgetBudget.referenced_entity_id = ' . BudgetRepository::TABLE_ALIAS . '.id AND RoleBudgetBudget.entity_id = :roleId'
            )
            ->setParameter('roleId', $filterStruct->aclGrantContext->getEntity()->id);
    }
}
