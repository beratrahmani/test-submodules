<?php declare(strict_types=1);

namespace Shopware\B2B\Company\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclReadHelper;

class CompanyFilterHelper
{
    /**
     * @param AclReadHelper $readHelper
     * @param CompanyAssignmentFilter $assignmentFilter
     * @param CompanyFilterStruct $filterStruct
     * @param QueryBuilder $queryBuilder
     * @param CompanyInheritanceFilter $companyInheritanceFilter
     */
    public function applyFilter(
        AclReadHelper $readHelper,
        CompanyAssignmentFilter $assignmentFilter,
        CompanyFilterStruct $filterStruct,
        QueryBuilder $queryBuilder,
        CompanyInheritanceFilter $companyInheritanceFilter
    ) {
        if (!$filterStruct->aclGrantContext) {
            return;
        }

        switch ($filterStruct->companyFilterType) {
            case CompanyFilterStruct::TYPE_VISIBILITY:
                $readHelper->applyAclFilter($filterStruct, $queryBuilder);

                return;
            case CompanyFilterStruct::TYPE_ASSIGNMENT:
                $assignmentFilter->applyFilter($filterStruct, $queryBuilder);

                return;
            case CompanyFilterStruct::TYPE_INHERITANCE:
                $companyInheritanceFilter->applyFilter($filterStruct, $queryBuilder);

                return;
        }

        throw new \RuntimeException(
            sprintf('Unable to determine the filter method %s.', $filterStruct->companyFilterType)
        );
    }
}
