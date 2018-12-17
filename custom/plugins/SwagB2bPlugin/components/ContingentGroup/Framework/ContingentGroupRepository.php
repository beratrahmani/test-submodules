<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Framework;

use Doctrine\DBAL\Connection;
use Shopware\B2B\Acl\Framework\AclReadHelper;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Company\Framework\CompanyFilterHelper;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContingentGroupRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_contingent_group';

    const TABLE_ALIAS = 'contingent_groups';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var AclReadHelper
     */
    private $aclReadHelper;

    /**
     * @var ContingentGroupCompanyAssignmentFilter
     */
    private $contingentGroupCompanyAssignmentFilter;

    /**
     * @var CompanyFilterHelper
     */
    private $companyFilterHelper;

    /**
     * @var ContingentGroupCompanyInheritanceFilter
     */
    private $contingentGroupCompanyInheritanceFilter;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param AclReadHelper $aclReadHelper
     * @param ContingentGroupCompanyAssignmentFilter $contingentGroupCompanyAssignmentFilter
     * @param CompanyFilterHelper $companyFilterHelper
     * @param ContingentGroupCompanyInheritanceFilter $contingentGroupCompanyInheritanceFilter
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        AclReadHelper $aclReadHelper,
        ContingentGroupCompanyAssignmentFilter $contingentGroupCompanyAssignmentFilter,
        CompanyFilterHelper $companyFilterHelper,
        ContingentGroupCompanyInheritanceFilter $contingentGroupCompanyInheritanceFilter
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->aclReadHelper = $aclReadHelper;
        $this->contingentGroupCompanyAssignmentFilter = $contingentGroupCompanyAssignmentFilter;
        $this->companyFilterHelper = $companyFilterHelper;
        $this->contingentGroupCompanyInheritanceFilter = $contingentGroupCompanyInheritanceFilter;
    }

    /**
     * @param OwnershipContext $context
     * @param ContingentGroupSearchStruct $searchStruct
     * @return ContingentGroupEntity[]
     */
    public function fetchList(OwnershipContext $context, ContingentGroupSearchStruct $searchStruct): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.context_owner_id = :owner')
            ->setParameter('owner', $context->contextOwnerId);

        $this->aclReadHelper->applyAclVisibility($context, $query);
        $this->companyFilterHelper
            ->applyFilter(
                $this->aclReadHelper,
                $this->contingentGroupCompanyAssignmentFilter,
                $searchStruct,
                $query,
                $this->contingentGroupCompanyInheritanceFilter
            );

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();

        $contingentGroupsData = $statement
            ->fetchAll(\PDO::FETCH_ASSOC);

        $contingentGroups = [];
        foreach ($contingentGroupsData as $contingentGroupData) {
            $contingentGroups[] = (new ContingentGroupEntity())->fromDatabaseArray($contingentGroupData);
        }

        return $contingentGroups;
    }

    /**
     * @param OwnershipContext $context
     * @param ContingentGroupSearchStruct $searchStruct
     * @param int $contactId
     * @return array
     */
    public function fetchListByContactId(
        OwnershipContext $context,
        ContingentGroupSearchStruct $searchStruct,
        int $contactId
    ): array {
        $query = $this->connection->createQueryBuilder()
            ->select(
                self::TABLE_ALIAS . '.id',
                self::TABLE_ALIAS . '.context_owner_id',
                self::TABLE_ALIAS . '.name',
                self::TABLE_ALIAS . '.description',
                'contactcontingent.id as assignmentId'
            )
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->leftJoin(
                self::TABLE_ALIAS,
                'b2b_contact_contingent_group',
                'contactcontingent',
                self::TABLE_ALIAS . '.id = contactcontingent.contingent_group_id and contactcontingent.contact_id = :contactId'
            )
            ->where(self::TABLE_ALIAS . '.context_owner_id = :owner')
            ->setParameter('owner', $context->contextOwnerId)
            ->setParameter('contactId', $contactId);

        $this->aclReadHelper->applyAclVisibility($context, $query);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();

        $contingentGroupsData = $statement
            ->fetchAll(\PDO::FETCH_ASSOC);

        $contingentGroups = [];
        foreach ($contingentGroupsData as $contingentGroupData) {
            $contingentGroups[] = (new ContingentGroupAssignmentEntity())->fromDatabaseArray($contingentGroupData);
        }

        return $contingentGroups;
    }

    /**
     * @param OwnershipContext $context
     * @param ContingentGroupSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(
        OwnershipContext $context,
        ContingentGroupSearchStruct $searchStruct
    ): int {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.context_owner_id = :owner')
            ->setParameter('owner', $context->contextOwnerId);

        $this->aclReadHelper->applyAclVisibility($context, $query);

        $this->companyFilterHelper
            ->applyFilter(
                $this->aclReadHelper,
                $this->contingentGroupCompanyAssignmentFilter,
                $searchStruct,
                $query,
                $this->contingentGroupCompanyInheritanceFilter
            );

        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param int $id
     * @param OwnershipContext $ownershipContext
     * @return ContingentGroupEntity
     */
    public function fetchOneById(int $id, OwnershipContext $ownershipContext): ContingentGroupEntity
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->andWhere(self::TABLE_ALIAS . '.context_owner_id = :contextOwner')
            ->setParameter('id', $id)
            ->setParameter('contextOwner', $ownershipContext->contextOwnerId);

        $contingentGroupData = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$contingentGroupData) {
            throw new NotFoundException(sprintf('Contingent Group not found for %s', $id));
        }

        $contingentGroup = new ContingentGroupEntity();

        $contingentGroup->fromDatabaseArray($contingentGroupData);

        return $contingentGroup;
    }

    /**
     * @param ContingentGroupEntity $contingentGroupEntity
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     * @return ContingentGroupEntity
     */
    public function addContingentGroup(
        ContingentGroupEntity $contingentGroupEntity,
        OwnershipContext $ownershipContext
    ): ContingentGroupEntity {
        if (!$contingentGroupEntity->isNew()) {
            throw new CanNotInsertExistingRecordException('The Contingent Group provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            array_merge(
                $contingentGroupEntity->toDatabaseArray(),
                ['context_owner_id' => $ownershipContext->contextOwnerId]
            )
        );

        $contingentGroupEntity->id = (int) $this->connection->lastInsertId();

        return $contingentGroupEntity;
    }

    /**
     * @param ContingentGroupEntity $contingentGroupEntity
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException
     * @return ContingentGroupEntity
     */
    public function updateContingentGroup(
        ContingentGroupEntity $contingentGroupEntity,
        OwnershipContext $ownershipContext
    ): ContingentGroupEntity {
        if ($contingentGroupEntity->isNew()) {
            throw new CanNotUpdateExistingRecordException('The Contingent Group provided does not exist');
        }

        $this->connection->update(
            self::TABLE_NAME,
            $contingentGroupEntity->toDatabaseArray(),
            [
                'id' => $contingentGroupEntity->id,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );

        return $contingentGroupEntity;
    }

    /**
     * @param ContingentGroupEntity $contingentGroupEntity
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveUsedRecordException
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException
     * @return ContingentGroupEntity
     */
    public function removeContingentGroup(
        ContingentGroupEntity $contingentGroupEntity,
        OwnershipContext $ownershipContext
    ): ContingentGroupEntity {
        if ($contingentGroupEntity->isNew()) {
            throw new CanNotRemoveExistingRecordException('The Contingent Group provided does not exist');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            [
                'id' => $contingentGroupEntity->id,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );

        $contingentGroupEntity->id = null;

        return $contingentGroupEntity;
    }

    /**
     * @return string query alias for filter construction
     */
    public function getMainTableAlias(): string
    {
        return self::TABLE_ALIAS;
    }

    /**
     * @return string[]
     */
    public function getFullTextSearchFields(): array
    {
        return [
            'name',
            'description',
        ];
    }

    /**
     * @param int $identityId
     * @return int[]
     */
    public function fetchContingentGroupIdsForContact(int $identityId): array
    {
        $contingentQueryRoles = $this->connection->createQueryBuilder()
            ->select('rcg.contingent_group_id')
            ->from('b2b_role_contingent_group', 'rcg')
            ->innerJoin('rcg', 'b2b_role_contact', 'rc', 'rcg.role_id = rc.role_id')
            ->where('rc.debtor_contact_id = :id')
            ->getSQL();

        $contingentQueryContacts = $this->connection->createQueryBuilder()
            ->select('ccg.contingent_group_id')
            ->from('b2b_contact_contingent_group', 'ccg')
            ->where('ccg.contact_id = :id')
            ->getSQL();

        $sql = "( {$contingentQueryRoles} ) UNION ( {$contingentQueryContacts} )";
        $contingentGroupData = $this->connection
            ->executeQuery($sql, ['id' => $identityId])
            ->fetchAll();

        $contingentGroups = [];
        foreach ($contingentGroupData as $group) {
            $contingentGroups[] = (int) $group['contingent_group_id'];
        }

        return $contingentGroups;
    }

    /**
     * @param int $groupId
     * @return array
     */
    public function fetchRuleTypesFromContingentGroup(int $groupId): array
    {
        $ruleTypes = [];
        $ruleTypesData = $this->connection->createQueryBuilder()
            ->select('DISTINCT(type)')
            ->from('b2b_contingent_group_rule', 'cgr')
            ->where('cgr.contingent_group_id = :id')
            ->setParameter(':id', $groupId)
            ->execute()
            ->fetchAll();

        foreach ($ruleTypesData as $type) {
            $ruleTypes[] = $type['type'];
        }

        return $ruleTypes;
    }

    /**
     * @param OwnershipContext $context
     * @param $searchStruct
     * @param int $roleId
     * @return ContingentGroupAssignmentEntity[]
     */
    public function fetchAllContingentGroupsWithCheckForRoleAssignment(
        OwnershipContext $context,
        ContingentGroupSearchStruct $searchStruct,
        int $roleId
    ): array {
        $query = $this->connection->createQueryBuilder()
            ->select(
                self::TABLE_ALIAS . '.id',
                self::TABLE_ALIAS . '.name',
                self::TABLE_ALIAS . '.description',
                self::TABLE_ALIAS . '.context_owner_id',
                'rc.id as assignmentId'
            )
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->leftJoin(
                self::TABLE_ALIAS,
                'b2b_role_contingent_group',
                'rc',
                self::TABLE_ALIAS . '.id = rc.contingent_group_id and rc.role_id = :id'
            )
            ->where(self::TABLE_ALIAS . '.context_owner_id = :contextId')
            ->setParameter('id', $roleId)
            ->setParameter('contextId', $context->contextOwnerId);

        $this->aclReadHelper->applyAclVisibility($context, $query);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();
        $groupData = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $groups = [];
        foreach ($groupData as $roleData) {
            $groups[] = (new ContingentGroupAssignmentEntity())->fromDatabaseArray($roleData);
        }

        return $groups;
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }
}
