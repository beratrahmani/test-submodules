<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Company\Framework\CompanyAssignmentFilter;
use Shopware\B2B\Company\Framework\CompanyFilterStruct;

class ContingentGroupCompanyAssignmentFilter implements CompanyAssignmentFilter
{
    /**
     * @param CompanyFilterStruct $filterStruct
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilter(CompanyFilterStruct $filterStruct, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->innerJoin(
                ContingentGroupRepository::TABLE_ALIAS,
                'b2b_role_contingent_group',
                'contingentGroupRole',
                'contingentGroupRole.contingent_group_id = contingent_groups.id AND contingentGroupRole.role_id = :roleId'
            )
            ->setParameter('roleId', $filterStruct->aclGrantContext->getEntity()->id);
    }
}
