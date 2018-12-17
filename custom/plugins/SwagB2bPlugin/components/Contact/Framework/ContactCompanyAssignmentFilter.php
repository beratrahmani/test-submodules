<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Company\Framework\CompanyAssignmentFilter;
use Shopware\B2B\Company\Framework\CompanyFilterStruct;

class ContactCompanyAssignmentFilter implements CompanyAssignmentFilter
{
    /**
     * @param CompanyFilterStruct $filterStruct
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilter(CompanyFilterStruct $filterStruct, QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->innerJoin(
                ContactRepository::TABLE_ALIAS,
                'b2b_role_contact',
                'contactRole',
                'contactRole.debtor_contact_id = contact.id AND contactRole.role_id = :roleId'
            )
            ->setParameter('roleId', $filterStruct->aclGrantContext->getEntity()->id);
    }
}
