<?php declare(strict_types=1);

namespace Shopware\B2B\Company\Framework;

use Doctrine\DBAL\Query\QueryBuilder;

interface CompanyInheritanceFilter
{
    /**
     * @param CompanyFilterStruct $filterStruct
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilter(CompanyFilterStruct $filterStruct, QueryBuilder $queryBuilder);
}
