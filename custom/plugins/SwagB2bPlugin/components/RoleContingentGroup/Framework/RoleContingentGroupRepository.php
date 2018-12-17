<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContingentGroup\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclReadHelper;
use Shopware\B2B\Role\Framework\RoleAclGrantContext;
use Shopware\B2B\Role\Framework\RoleAssignmentEntity;
use Shopware\B2B\Role\Framework\RoleRemoveDependencyValidatorInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

/**
 * DB-Representation of role:contact assignment
 */
class RoleContingentGroupRepository implements RoleRemoveDependencyValidatorInterface
{
    const TABLE_NAME = 'b2b_role_contingent_group';
    const TABLE_ALIAS = 'roles_contingent_groups';
    const TABLE_NAME_ROLE = 'b2b_role';
    const TABLE_ALIAS_ROLE = 'role';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var AclReadHelper
     */
    private $aclReadHelper;

    /**
     * @param Connection $connection
     * @param AclReadHelper $aclReadHelper
     */
    public function __construct(Connection $connection, AclReadHelper $aclReadHelper)
    {
        $this->connection = $connection;
        $this->aclReadHelper = $aclReadHelper;
    }

    /**
     * @param int $roleId
     * @param int $contingentGroupId
     */
    public function removeRoleContingentGroupAssignment(int $roleId, int $contingentGroupId)
    {
        $this->connection->delete(
            self::TABLE_NAME,
            [
                'role_id' => $roleId,
                'contingent_group_id' => $contingentGroupId,
            ]
        );
    }

    /**
     * @param int $roleId
     * @param OwnershipContext $ownershipContext
     * @return RoleAssignmentEntity[]
     */
    public function fetchAllRolesAndCheckForContingentGroupAssignment(
        int $roleId,
        OwnershipContext $ownershipContext
    ): array {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS_ROLE . '.*')
            ->addSelect(self::TABLE_ALIAS . '.id as assignmentId')
            ->addSelect('(' . self::TABLE_ALIAS_ROLE . '.left + 1 != ' . self::TABLE_ALIAS_ROLE . '.right) as hasChildren')
            ->from(self::TABLE_NAME_ROLE, self::TABLE_ALIAS_ROLE)
            ->leftJoin(self::TABLE_ALIAS_ROLE, self::TABLE_NAME, self::TABLE_ALIAS, self::TABLE_ALIAS_ROLE . '.id = ' . self::TABLE_ALIAS . '.role_id')
            ->where(self::TABLE_ALIAS_ROLE . '.id = :roleId')
            ->andWhere(self::TABLE_ALIAS_ROLE . '.context_owner_id = :contextOwnerId')
            ->setParameter('roleId', $roleId)
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId)
            ->execute();

        $rolesData = $query->fetchAll(\PDO::FETCH_ASSOC);

        $roles = [];
        foreach ($rolesData as $roleData) {
            $roles[] = (new RoleAssignmentEntity())->fromDatabaseArray($roleData);
        }

        return $roles;
    }

    /**
     * @param int $roleId
     * @param int $contingentGroupId
     */
    public function assignRoleContingentGroup(int $roleId, int $contingentGroupId)
    {
        $data = [
            'role_id' => $roleId,
            'contingent_group_id' => $contingentGroupId,
        ];

        $this->connection->insert(
            self::TABLE_NAME,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function extendRoleRemoveDependencyQuery(QueryBuilder $queryBuilder, RoleAclGrantContext $grantContext, string $mainTableAlias, string $alias)
    {
        $queryBuilder->leftJoin($mainTableAlias, self::TABLE_NAME, $alias, "{$alias}.role_id = {$mainTableAlias}.id");
        $queryBuilder->orWhere("{$alias}.id is not null");
    }
}
