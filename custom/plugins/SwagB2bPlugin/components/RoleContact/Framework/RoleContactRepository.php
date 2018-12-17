<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclReadHelper;
use Shopware\B2B\Role\Framework\AbstractRoleBaseRepository;
use Shopware\B2B\Role\Framework\RoleAclGrantContext;
use Shopware\B2B\Role\Framework\RoleAssignmentEntity;
use Shopware\B2B\Role\Framework\RoleRemoveDependencyValidatorInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\DbalNestedSet\NestedSetQueryFactory;

/**
 * DB-Representation of role:contact assignment
 */
class RoleContactRepository extends AbstractRoleBaseRepository implements RoleRemoveDependencyValidatorInterface
{
    const TABLE_ROLE_CONTACT_NAME = 'b2b_role_contact';
    const TABLE_ROLE_CONTACT_ALIAS = 'role_contact';

    /**
     * @var NestedSetQueryFactory
     */
    private $nestedSetQueryFactory;

    /**
     * @param Connection $connection
     * @param NestedSetQueryFactory $nestedSetQueryFactory
     * @param AclReadHelper $aclReadHelper
     */
    public function __construct(
        Connection $connection,
        NestedSetQueryFactory $nestedSetQueryFactory,
        AclReadHelper $aclReadHelper
    ) {
        parent::__construct($connection, $aclReadHelper);
        $this->nestedSetQueryFactory = $nestedSetQueryFactory;
    }

    /**
     * @param int $roleId
     * @param int $contactId
     */
    public function removeRoleContactAssignment(int $roleId, int $contactId)
    {
        $this->connection->delete(
            self::TABLE_ROLE_CONTACT_NAME,
            [
                'role_id' => $roleId,
                'debtor_contact_id' => $contactId,
            ]
        );
    }

    /**
     * @param int $roleId
     * @param int $contactId
     */
    public function assignRoleContact(int $roleId, int $contactId)
    {
        $data = [
            'role_id' => $roleId,
            'debtor_contact_id' => $contactId,
        ];

        $this->connection->insert(
            self::TABLE_ROLE_CONTACT_NAME,
            $data
        );
    }

    /**
     * @param int $contactId
     * @return array
     */
    public function getActiveRoleIdsByContactId(int $contactId): array
    {
        $roles = $this->connection->fetchAll(
            'SELECT role_id FROM ' . self::TABLE_ROLE_CONTACT_NAME . '
             WHERE debtor_contact_id = :contactId
            ',
            [
                ':contactId' => $contactId,
            ]
        );

        return $roles;
    }

    /**
     * @param int $parentId
     * @param int $contactId
     * @param OwnershipContext $ownershipContext
     * @return RoleAssignmentEntity[]
     */
    public function fetchChildrenAndCheckForContactAssignment(
        int $parentId,
        int $contactId,
        OwnershipContext $ownershipContext
    ): array {
        $query = $this->nestedSetQueryFactory
            ->createChildrenQueryBuilder(self::TABLE_ROLE_NAME, self::TABLE_ROLE_ALIAS, 'context_owner_id', $parentId)
            ->addSelect(self::TABLE_ROLE_ALIAS . '.*')
            ->addSelect('(role.left + 1 != role.right) as hasChildren')
            ->addSelect(self::TABLE_ROLE_CONTACT_ALIAS . '.id as assignmentId')
            ->leftJoin(
                self::TABLE_ROLE_ALIAS,
                self::TABLE_ROLE_CONTACT_NAME,
                self::TABLE_ROLE_CONTACT_ALIAS,
                self::TABLE_ROLE_ALIAS . '.id = ' . self::TABLE_ROLE_CONTACT_ALIAS . '.role_id and ' . self::TABLE_ROLE_CONTACT_ALIAS . '.debtor_contact_id = :contactId'
            )
            ->andWhere(self::TABLE_ROLE_ALIAS . '.context_owner_id = :contextOwner')
            ->setParameter('contextOwner', $ownershipContext->contextOwnerId)
            ->setParameter('contactId', $contactId);

        $statement = $query->execute();
        $rolesData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $roles = [];
        foreach ($rolesData as $roleData) {
            $roles[] = (new RoleAssignmentEntity())->fromDatabaseArray($roleData);
        }

        return $roles;
    }

    /**
     * @param int $roleId
     * @param int $contactId
     * @param OwnershipContext $ownershipContext
     * @return bool
     */
    public function isRoleDebtorContactDebtor(OwnershipContext $ownershipContext, int $roleId, int $contactId): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select('role.id')
            ->from('b2b_role', 'role')
            ->leftJoin('role', 'b2b_debtor_contact', 'contact', 'contact.context_owner_id = role.context_owner_id')
            ->where('role.id = :roleId AND contact.id = :contactId AND contact.context_owner_id = :contextOwnerId')
            ->setParameter('contactId', $contactId)
            ->setParameter('roleId', $roleId)
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId)
            ->execute();

        return (bool) $query->fetch(\PDO::FETCH_COLUMN);
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
        $queryBuilder->leftJoin(
            $mainTableAlias,
            self::TABLE_ROLE_CONTACT_NAME,
            $alias,
            "{$alias}.role_id = {$mainTableAlias}.id"
        );
        $queryBuilder->orWhere("{$alias}.id is not null");
    }

    /**
     * @param int $contactId
     * @param OwnershipContext $ownershipContext
     * @return RoleAssignmentEntity[]
     */
    public function fetchRootRoleAssignmentsAndCheckForContactAssignment(
        int $contactId,
        OwnershipContext $ownershipContext
    ): array {
        $query = $this->createRootRolesQueryBuilder($ownershipContext, false)
            ->addSelect(self::TABLE_ROLE_CONTACT_ALIAS . '.id as assignmentId')
            ->leftJoin(
                self::TABLE_ROLE_ALIAS,
                self::TABLE_ROLE_CONTACT_NAME,
                self::TABLE_ROLE_CONTACT_ALIAS,
                self::TABLE_ROLE_ALIAS . '.id = ' . self::TABLE_ROLE_CONTACT_ALIAS . '.role_id and ' . self::TABLE_ROLE_CONTACT_ALIAS . '.debtor_contact_id = :contactId'
            )
            ->setParameter('contactId', $contactId);

        $statement = $query->execute();
        $rolesData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $roles = [];
        foreach ($rolesData as $roleData) {
            $roles[] = (new RoleAssignmentEntity())->fromDatabaseArray($roleData);
        }

        return $roles;
    }
}
