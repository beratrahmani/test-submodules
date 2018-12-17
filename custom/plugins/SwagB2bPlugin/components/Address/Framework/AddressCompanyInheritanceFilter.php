<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Company\Framework\CompanyFilterStruct;
use Shopware\B2B\Company\Framework\CompanyInheritanceFilter;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\DbalNestedSet\NestedSetQueryFactory;

class AddressCompanyInheritanceFilter implements CompanyInheritanceFilter
{
    /**
     * @var NestedSetQueryFactory
     */
    private $nestedSetQueryFactory;

    /**
     * @param NestedSetQueryFactory $nestedSetQueryFactory
     */
    public function __construct(
        NestedSetQueryFactory $nestedSetQueryFactory
    ) {
        $this->nestedSetQueryFactory = $nestedSetQueryFactory;
    }

    /**
     * @param CompanyFilterStruct $filterStruct
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilter(CompanyFilterStruct $filterStruct, QueryBuilder $queryBuilder)
    {
        $inIdsQuery = $this->nestedSetQueryFactory
            ->createParentAndChildrenQueryBuilder(
                RoleRepository::TABLE_ROLE_NAME,
                RoleRepository::TABLE_ROLE_ALIAS,
                'context_owner_id',
                $filterStruct->aclGrantContext->getEntity()->id
            )
            ->select('RoleAddress.referenced_entity_id')
            ->innerJoin(
                RoleRepository::TABLE_ROLE_ALIAS,
                'b2b_acl_role_address',
                'RoleAddress',
                'RoleAddress.entity_id = ' . RoleRepository::TABLE_ROLE_ALIAS . '.id'
            );

        $queryBuilder->andWhere($queryBuilder->expr()->in(AddressRepositoryInterface::TABLE_ALIAS . '.id', $inIdsQuery->getSQL()));

        foreach ($inIdsQuery->getParameters() as $column => $value) {
            $queryBuilder->setParameter($column, $value);
        }
    }
}
