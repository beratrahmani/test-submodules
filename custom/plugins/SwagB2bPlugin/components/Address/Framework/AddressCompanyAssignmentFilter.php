<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Company\Framework\CompanyAssignmentFilter;
use Shopware\B2B\Company\Framework\CompanyFilterStruct;

class AddressCompanyAssignmentFilter implements CompanyAssignmentFilter
{
    /**
     * @param CompanyFilterStruct $filterStruct
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilter(CompanyFilterStruct $filterStruct, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->innerJoin(
                AddressRepositoryInterface::TABLE_ALIAS,
                'b2b_acl_role_address',
                'RoleAddress',
                'RoleAddress.referenced_entity_id = ' . AddressRepositoryInterface::TABLE_ALIAS . '.id AND RoleAddress.entity_id = :roleId'
            )
            ->setParameter('roleId', $filterStruct->aclGrantContext->getEntity()->id);
    }
}
