<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclReadHelper;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

abstract class AbstractRoleBaseRepository
{
    const TABLE_ROLE_NAME = 'b2b_role';
    const TABLE_ROLE_ALIAS = 'role';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var AclReadHelper
     */
    protected $aclReadHelper;

    /**
     * @param Connection $connection
     * @param AclReadHelper $aclReadHelper
     */
    public function __construct(
        Connection $connection,
        AclReadHelper $aclReadHelper
    ) {
        $this->connection = $connection;
        $this->aclReadHelper = $aclReadHelper;
    }

    /**
     * @internal
     * @param OwnershipContext $ownershipContext
     * @param bool $withTechnicalRoot
     * @return QueryBuilder
     */
    protected function createRootRolesQueryBuilder(
        OwnershipContext $ownershipContext,
        bool $withTechnicalRoot
    ): QueryBuilder {
        $visibleRolesQuery = $this->connection->createQueryBuilder();
        $visibleRolesQuery
            ->select(self::TABLE_ROLE_ALIAS . '.id')
            ->from(self::TABLE_ROLE_NAME, self::TABLE_ROLE_ALIAS);

        $this->aclReadHelper->applyAclVisibility($ownershipContext, $visibleRolesQuery);

        $filterQuery = $this->connection->createQueryBuilder();
        $filterQuery
            ->select('*')
            ->from(self::TABLE_ROLE_NAME, self::TABLE_ROLE_ALIAS . 'Inner')
            ->where($filterQuery->expr()->in(self::TABLE_ROLE_ALIAS . 'Inner.id', $visibleRolesQuery->getSQL()));

        $resultQuery = $this->connection->createQueryBuilder();
        $resultQuery
            ->select(self::TABLE_ROLE_ALIAS . '.*')
            ->addSelect('(role.left + 1 != role.right) as hasChildren')
            ->from(self::TABLE_ROLE_NAME, self::TABLE_ROLE_ALIAS);

        $this->aclReadHelper->applyAclVisibility($ownershipContext, $resultQuery);

        $resultQuery
            ->leftJoin(
                self::TABLE_ROLE_ALIAS,
                '(' . $filterQuery->getSQL() . ')',
                self::TABLE_ROLE_ALIAS . 'Left',
                self::TABLE_ROLE_ALIAS . 'Left.context_owner_id = ' . self::TABLE_ROLE_ALIAS . '.context_owner_id AND ' .
                self::TABLE_ROLE_ALIAS . 'Left.id <> ' . self::TABLE_ROLE_ALIAS . '.id AND ' .
                self::TABLE_ROLE_ALIAS . '.left BETWEEN ' . self::TABLE_ROLE_ALIAS . 'Left.left AND ' . self::TABLE_ROLE_ALIAS . 'Left.right' . ' AND ' .
                self::TABLE_ROLE_ALIAS . 'Left.level >= :minLevel'
            )
            ->where(self::TABLE_ROLE_ALIAS . 'Left.id IS NULL')
            ->andWhere(self::TABLE_ROLE_ALIAS . '.context_owner_id = :contextOwner')
            ->andWhere(self::TABLE_ROLE_ALIAS . '.level >= :minLevel')
            ->setParameters(
                array_merge(
                    $resultQuery->getParameters(),
                    $visibleRolesQuery->getParameters()
                )
            )
            ->setParameter('contextOwner', $ownershipContext->contextOwnerId)
            ->setParameter('minLevel', (int) !$withTechnicalRoot);

        return $resultQuery;
    }
}
