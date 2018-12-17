<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclReadHelper;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\DbalNestedSet\NestedSetQueryFactory;
use Shopware\DbalNestedSet\NestedSetWriter;

class RoleRepository extends AbstractRoleBaseRepository implements GridRepository
{
    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var NestedSetQueryFactory
     */
    private $nestedSetQueryFactory;

    /**
     * @var NestedSetWriter
     */
    private $nestedSetWriter;

    /**
     * @var AclRoleRemoveDependencyValidator[]
     */
    private $dependencyValidators;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param NestedSetQueryFactory $nestedSetQueryFactory
     * @param NestedSetWriter $nestedSetWriter
     * @param AclReadHelper $aclReadHelper
     * @param AclRoleRemoveDependencyValidator[] $dependencyValidators
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        NestedSetQueryFactory $nestedSetQueryFactory,
        NestedSetWriter $nestedSetWriter,
        AclReadHelper $aclReadHelper,
        array $dependencyValidators
    ) {
        parent::__construct($connection, $aclReadHelper);
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->nestedSetQueryFactory = $nestedSetQueryFactory;
        $this->nestedSetWriter = $nestedSetWriter;
        $this->dependencyValidators = $dependencyValidators;
    }

    /**
     * @param RoleSearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    public function fetchList(RoleSearchStruct $searchStruct, OwnershipContext $ownershipContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->addSelect('(role.left + 1 != role.right) as hasChildren')
            ->from(self::TABLE_ROLE_NAME, self::TABLE_ROLE_ALIAS)
            ->where(self::TABLE_ROLE_ALIAS . '.context_owner_id = :contextOwnerId')
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ROLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);
        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $statement = $query->execute();
        $rolesData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $roles = [];
        foreach ($rolesData as $roleData) {
            $roles[] = (new RoleEntity())->fromDatabaseArray($roleData);
        }

        return $roles;
    }

    /**
     * @param RoleSearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return int
     */
    public function fetchTotalCount(RoleSearchStruct $searchStruct, OwnershipContext $ownershipContext): int
    {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_ROLE_NAME, self::TABLE_ROLE_ALIAS)
            ->where(self::TABLE_ROLE_ALIAS . '.context_owner_id = :contextOwnerId')
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId);

        $this->dbalHelper->applyFilters($searchStruct, $query);
        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn();
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @return RoleEntity
     */
    public function fetchRoot(OwnershipContext $ownershipContext): RoleEntity
    {
        $rawRole = $this->nestedSetQueryFactory
            ->createFetchRootsQueryBuilder(self::TABLE_ROLE_NAME, self::TABLE_ROLE_ALIAS)
            ->addSelect(self::TABLE_ROLE_ALIAS . '.*')
            ->addSelect('(role.left + 1 != role.right) as hasChildren')
            ->andWhere('role.context_owner_id = :contextOwnerId')
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$rawRole) {
            $rootId = $this->nestedSetWriter
                ->insertRoot(
                    self::TABLE_ROLE_NAME,
                    'context_owner_id',
                    $ownershipContext->contextOwnerId,
                    ['name' => 'root']
                );

            return $this->fetchOneById($rootId, $ownershipContext);
        }

        $role = new RoleEntity();

        $role->fromDatabaseArray($rawRole);

        return $role;
    }

    /**
     * @param int $parentId
     * @param OwnershipContext $ownershipContext
     * @return RoleEntity[]
     */
    public function fetchChildren(int $parentId, OwnershipContext $ownershipContext): array
    {
        $query = $this->nestedSetQueryFactory
            ->createChildrenQueryBuilder(self::TABLE_ROLE_NAME, self::TABLE_ROLE_ALIAS, 'context_owner_id', $parentId)
            ->addSelect(self::TABLE_ROLE_ALIAS . '.*')
            ->addSelect('(role.left + 1 != role.right) as hasChildren')
            ->andWhere(self::TABLE_ROLE_ALIAS . '.context_owner_id = :contextOwner')
            ->setParameter('contextOwner', $ownershipContext->contextOwnerId);

        return $this->fetchRoles($query);
    }

    /**
     * @param int[] $selectedRoleIds
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    public function fetchSubtree(array $selectedRoleIds, OwnershipContext $ownershipContext): array
    {
        $query = $this->nestedSetQueryFactory
            ->createSubtreeThroughMultipleNodesQueryBuilder(
                self::TABLE_ROLE_NAME,
                self::TABLE_ROLE_ALIAS,
                'context_owner_id',
                $selectedRoleIds
            )
            ->addSelect(self::TABLE_ROLE_ALIAS . '.*')
            ->addSelect('(role.left + 1 != role.right) as hasChildren')
            ->andWhere('role.context_owner_id = :contextOwnerId')
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId);

        return $this->fetchRoles($query);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param bool $withTechnicalRoot
     * @return RoleEntity[]
     */
    public function fetchAclRootRoles(OwnershipContext $ownershipContext, bool $withTechnicalRoot): array
    {
        $query = $this->createRootRolesQueryBuilder($ownershipContext, $withTechnicalRoot);

        return $this->fetchRoles($query);
    }

    /**
     * @param RoleEntity $roleEntity
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException
     * @return RoleEntity
     */
    public function removeRole(RoleEntity $roleEntity): RoleEntity
    {
        if ($roleEntity->isNew()) {
            throw new CanNotRemoveExistingRecordException('The role provided does not exist');
        }

        $this->nestedSetWriter->removeNode(
            self::TABLE_ROLE_NAME,
            'context_owner_id',
            $roleEntity->id
        );

        $roleEntity->id = null;

        return $roleEntity;
    }

    /**
     * @param RoleEntity $role
     * @param int $parentId
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     * @return RoleEntity
     */
    public function addRole(RoleEntity $role, int $parentId): RoleEntity
    {
        if (!$role->isNew()) {
            throw new CanNotInsertExistingRecordException('The role provided already exists');
        }

        $role->id = $this->nestedSetWriter
            ->insertAsLastChild(
                self::TABLE_ROLE_NAME,
                'context_owner_id',
                $parentId,
                $role->toDatabaseArray()
            );

        return $role;
    }

    /**
     * @param RoleEntity $role
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException
     * @return RoleEntity
     */
    public function updateRole(RoleEntity $role, OwnershipContext $ownershipContext): RoleEntity
    {
        if ($role->isNew()) {
            throw new CanNotUpdateExistingRecordException('The role provided does not exist');
        }

        $this->connection->update(
            self::TABLE_ROLE_NAME,
            $role->toDatabaseArray(),
            [
                'id' => $role->id,
                'context_owner_id' => $role->contextOwnerId,
            ]
        );

        return $this->fetchOneById($role->id, $ownershipContext);
    }

    /**
     * @param int $roleId
     * @param int $siblingId
     */
    public function moveRoleAsPrevSibling(int $roleId, int $siblingId)
    {
        $this->nestedSetWriter
            ->moveAsPrevSibling(
                self::TABLE_ROLE_NAME,
                'context_owner_id',
                $siblingId,
                $roleId
            );
    }

    /**
     * @param int $roleId
     * @param int $siblingId
     */
    public function moveRoleAsNextSibling(int $roleId, int $siblingId)
    {
        $this->nestedSetWriter
            ->moveAsNextSibling(
                self::TABLE_ROLE_NAME,
                'context_owner_id',
                $siblingId,
                $roleId
            );
    }

    /**
     * @param int $roleId
     * @param int $parentId
     */
    public function moveRoleAsLastChild(int $roleId, int $parentId)
    {
        $this->nestedSetWriter
            ->moveAsLastChild(
                self::TABLE_ROLE_NAME,
                'context_owner_id',
                $parentId,
                $roleId
            );
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     * @return RoleEntity
     */
    public function fetchOneById(int $id, OwnershipContext $ownershipContext): RoleEntity
    {
        $query = $this->connection->createQueryBuilder()
            ->select('child.*')
            ->addSelect('(child.left + 1 != child.right) as hasChildren')
            ->from(self::TABLE_ROLE_NAME, 'child')
            ->innerJoin(
                'child',
                self::TABLE_ROLE_NAME,
                self::TABLE_ROLE_ALIAS,
                'child.context_owner_id = ' . self::TABLE_ROLE_ALIAS . '.context_owner_id AND 
                child.`left` >= ' . self::TABLE_ROLE_ALIAS . '.`left` AND 
                child.`right` <= ' . self::TABLE_ROLE_ALIAS . '.`right`'
            )
            ->where('child.id = :id')
            ->andWhere(self::TABLE_ROLE_ALIAS . '.context_owner_id = :context')
            ->setParameter('id', $id);

        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $rootQuery = $this->connection->createQueryBuilder()
            ->select('child2.*')
            ->addSelect('(child2.left + 1 != child2.right) as hasChildren')
            ->from(self::TABLE_ROLE_NAME, 'child2')
            ->where('child2.id = :id')
            ->andWhere('child2.level = 0')
            ->andWhere('child2.context_owner_id = :context');

        $statement = $this->connection->executeQuery(
            $query->getSQL() . ' UNION ' . $rootQuery->getSQL(),
            array_merge(
                $query->getParameters(),
                [
                    'context' => $ownershipContext->contextOwnerId,
                ]
            )
        );

        $roleData = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$roleData) {
            throw new NotFoundException(sprintf('Role not found for %s', $id));
        }

        $entity = new RoleEntity();
        $entity->fromDatabaseArray($roleData);

        return $entity;
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @return RoleEntity
     */
    public function fetchParentByChildId(int $id, OwnershipContext $ownershipContext): RoleEntity
    {
        $roleData = $this->nestedSetQueryFactory
            ->createParentsQueryBuilder(self::TABLE_ROLE_NAME, self::TABLE_ROLE_ALIAS, 'context_owner_id', $id)
            ->select('*')
            ->addSelect('(role.left + 1 != role.right) as hasChildren')
            ->andWhere(self::TABLE_ROLE_ALIAS . '.context_owner_id = :contextOwner')
            ->setParameter('contextOwner', $ownershipContext->contextOwnerId)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (!$roleData) {
            throw new NotFoundException(sprintf('Role not found for %s', $id));
        }

        $entity = new RoleEntity();
        $entity->fromDatabaseArray($roleData);

        return $entity;
    }

    /**
     * @return string
     */
    public function getMainTableAlias(): string
    {
        return self::TABLE_ROLE_ALIAS;
    }

    /**
     * @return string[]
     */
    public function getFullTextSearchFields(): array
    {
        return [
            'name',
        ];
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }

    /**
     * @internal
     * @param QueryBuilder $query
     * @return RoleEntity[]
     */
    protected function fetchRoles(QueryBuilder $query): array
    {
        $rows = $query->execute()->fetchAll();

        $roles = [];
        foreach ($rows as $row) {
            $roles[] = (new RoleEntity())->fromDatabaseArray($row);
        }

        return $roles;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param RoleEntity $roleEntity
     * @return bool
     */
    public function isRoleRemovable(OwnershipContext $ownershipContext, RoleEntity $roleEntity): bool
    {
        $subQuery = $this->nestedSetQueryFactory
            ->createParentAndChildrenQueryBuilder(
                self::TABLE_ROLE_NAME,
                self::TABLE_ROLE_ALIAS,
                'context_owner_id',
                $roleEntity->id
            )
            ->select('role.*')
            ->setParameter(self::TABLE_ROLE_ALIAS . 'Root', $ownershipContext->contextOwnerId);

        $mainQuery = $this->connection->createQueryBuilder()
            ->select('count(*)')
            ->from('(' . $subQuery->getSQL() . ')', self::TABLE_ROLE_ALIAS)
            ->setParameters($subQuery->getParameters())
            ->setParameter('id', $roleEntity->id);

        $grantContext = new RoleAclGrantContext($roleEntity);

        foreach ($this->dependencyValidators as $key => $dependencyValidator) {
            $tableAlias = 'dependency_' . $key;
            $dependencyValidator->extendRoleRemoveDependencyQuery(
                $mainQuery,
                $grantContext,
                self::TABLE_ROLE_ALIAS,
                $tableAlias
            );
        }

        return !(bool) $mainQuery->execute()->fetch(\PDO::FETCH_COLUMN);
    }
}
