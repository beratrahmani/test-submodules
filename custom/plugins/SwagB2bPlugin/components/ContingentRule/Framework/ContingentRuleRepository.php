<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyAware;
use Shopware\B2B\Currency\Framework\CurrencyCalculator;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\ProductName\Framework\ProductNameAware;
use Shopware\B2B\ProductName\Framework\ProductNameService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ContingentRuleRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_contingent_group_rule';

    const TABLE_ALIAS = 'contingentGroupRule';

    /**
     * @var array
     */
    private static $mainTableFields = [
        'id',
        'contingent_group_id',
        'type',
    ];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var ContingentRuleTypeFactory
     */
    private $entityFactory;

    /**
     * @var CurrencyCalculator
     */
    private $currencyCalculator;

    /**
     * @var ProductNameService
     */
    private $productNameService;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param ContingentRuleTypeFactory $entityFactory
     * @param CurrencyCalculator $currencyCalculator
     * @param ProductNameService $productNameService
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        ContingentRuleTypeFactory $entityFactory,
        CurrencyCalculator $currencyCalculator,
        ProductNameService $productNameService
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->entityFactory = $entityFactory;
        $this->currencyCalculator = $currencyCalculator;
        $this->productNameService = $productNameService;
    }

    /**
     * @param array $types
     * @param int $contingentGroupId
     * @param ContingentRuleSearchStruct $searchStruct
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return array|ContingentRuleEntity[]
     */
    public function fetchList(
        array $types,
        int $contingentGroupId,
        ContingentRuleSearchStruct $searchStruct,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): array {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.contingent_group_id = :contingentGroupId')
            ->andWhere(self::TABLE_ALIAS . '.type IN (:types)')
            ->setParameter('contingentGroupId', $contingentGroupId)
            ->setParameter('types', $types, Connection::PARAM_STR_ARRAY);

        $this->applyTypeQueries($types, $query);
        $this->filterContextOwner($query, $ownershipContext);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();

        $contingentRulesData = $statement
            ->fetchAll(\PDO::FETCH_ASSOC);

        $contingentRules = [];
        foreach ($contingentRulesData as $contingentRuleData) {
            $entity = $this->entityFactory
                ->createEntityFromTypeName($contingentRuleData['type'])
                ->fromDatabaseArrayPrefixed($contingentRuleData);

            if ($entity instanceof ProductNameAware) {
                $this->productNameService->translateProductName($entity);
            }

            if ($entity instanceof CurrencyAware) {
                $this->currencyCalculator
                    ->recalculateAmount($entity, $currencyContext);
            }

            $contingentRules[] = $entity;
        }

        return $contingentRules;
    }

    /**
     * @param int $contingentGroupId
     * @param ContingentRuleSearchStruct $searchStruct
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return array|ContingentRuleEntity[]
     */
    public function fetchListByContingentGroupId(
        int $contingentGroupId,
        ContingentRuleSearchStruct $searchStruct,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): array {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.contingent_group_id = :contingentGroupId')
            ->setParameter('contingentGroupId', $contingentGroupId);

        $this->filterContextOwner($query, $ownershipContext);

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();

        $contingentRulesData = $statement
            ->fetchAll(\PDO::FETCH_ASSOC);

        $types = [];
        foreach ($contingentRulesData as $contingentRule) {
            $types[$contingentRule['type']] = true;
        }

        $this->applyTypeQueries(array_keys($types), $query);

        $statement = $query->execute();

        $contingentRulesData = $statement
            ->fetchAll(\PDO::FETCH_ASSOC);

        $contingentRules = [];
        foreach ($contingentRulesData as $contingentRuleData) {
            $entity = $this->entityFactory
                ->createEntityFromTypeName($contingentRuleData['type'])
                ->fromDatabaseArrayPrefixed($contingentRuleData);

            if ($entity instanceof ProductNameAware) {
                $this->productNameService->translateProductName($entity);
            }

            if ($entity instanceof CurrencyAware) {
                $this->currencyCalculator
                    ->recalculateAmount($entity, $currencyContext);
            }

            $contingentRules[] = $entity;
        }

        return $contingentRules;
    }

    /**
     * @param array $types
     * @param int $contingentGroupId
     * @param ContingentRuleSearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return int
     */
    public function fetchTotalCount(
        array $types,
        int $contingentGroupId,
        ContingentRuleSearchStruct $searchStruct,
        OwnershipContext $ownershipContext
    ): int {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.contingent_group_id = :contingentGroupId')
            ->andWhere(self::TABLE_ALIAS . '.type IN (:types)')
            ->setParameter('contingentGroupId', $contingentGroupId)
            ->setParameter('types', $types, Connection::PARAM_STR_ARRAY);

        $this->filterContextOwner($query, $ownershipContext);
        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param int $contingentGroupId
     * @param ContingentRuleSearchStruct $searchStruct
     * @param OwnershipContext $ownershipContext
     * @return int
     */
    public function fetchTotalCountByContingentGroupId(
        int $contingentGroupId,
        ContingentRuleSearchStruct $searchStruct,
        OwnershipContext $ownershipContext
    ): int {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.contingent_group_id = :contingentGroupId')
            ->setParameter('contingentGroupId', $contingentGroupId);

        $this->filterContextOwner($query, $ownershipContext);
        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param int $contingentRuleId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return ContingentRuleEntity
     */
    public function fetchOneById(
        int $contingentRuleId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): ContingentRuleEntity {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $contingentRuleId);

        $this->filterContextOwner($query, $ownershipContext);

        $contingentRule = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$contingentRule) {
            throw new NotFoundException("The contingent rule {$contingentRuleId} was not found");
        }

        $this->applyTypeQueries([$contingentRule['type']], $query);

        $contingentRuleData = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!isset($contingentRuleData[$contingentRule['type'] . '_contingent_rule_id'])) {
            throw new NotFoundException();
        }

        $entity = $this->entityFactory
            ->createEntityFromTypeName($contingentRuleData['type']);

        $entity->fromDatabaseArrayPrefixed($contingentRuleData);

        if ($entity instanceof ProductNameAware) {
            $this->productNameService->translateProductName($entity);
        }

        if ($entity instanceof CurrencyAware) {
            $this->currencyCalculator
                ->recalculateAmount($entity, $currencyContext);
        }

        return $entity;
    }

    /**
     * @param ContingentRuleEntity $contingentRuleEntity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException
     * @return ContingentRuleEntity
     */
    public function addContingentRule(ContingentRuleEntity $contingentRuleEntity): ContingentRuleEntity
    {
        if (!$contingentRuleEntity->isNew()) {
            throw new CanNotInsertExistingRecordException('The Contingent rule provided already exists');
        }

        $this->dbalHelper->transact(
            function () use ($contingentRuleEntity) {
                list($baseTableData, $extendedTableData) = $this->splitBaseAndExtendedTableData($contingentRuleEntity);
                $extendedTableName = $this->extractExtendedTableName($contingentRuleEntity);

                $this->connection->insert(
                    self::TABLE_NAME,
                    $baseTableData
                );

                $contingentRuleEntity->id = (int) $this->connection->lastInsertId();
                $extendedTableData['contingent_rule_id'] = $contingentRuleEntity->id;

                $this->connection->insert(
                    $extendedTableName,
                    $extendedTableData
                );
            }
        );

        return $contingentRuleEntity;
    }

    /**
     * @param ContingentRuleEntity $contingentRuleEntity
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException
     * @return ContingentRuleEntity
     */
    public function updateContingentRule(ContingentRuleEntity $contingentRuleEntity): ContingentRuleEntity
    {
        if ($contingentRuleEntity->isNew()) {
            throw new CanNotUpdateExistingRecordException('The Contingent rule provided does not exist');
        }

        if (!$this->isEqualTypeInDatabase($contingentRuleEntity)) {
            $this->removeContingentRule($contingentRuleEntity);

            return $this->addContingentRule($contingentRuleEntity);
        }

        $this->connection;
        $this->dbalHelper->transact(
            function () use ($contingentRuleEntity) {
                list($baseTableData, $extendedTableData) = $this->splitBaseAndExtendedTableData($contingentRuleEntity);
                $extendedTableName = $this->extractExtendedTableName($contingentRuleEntity);

                $this->connection->update(
                    self::TABLE_NAME,
                    $baseTableData,
                    ['id' => $contingentRuleEntity->id]
                );

                $this->connection->update(
                    $extendedTableName,
                    $extendedTableData,
                    ['contingent_rule_id' => $contingentRuleEntity->id]
                );
            }
        );

        return $contingentRuleEntity;
    }

    /**
     * @param ContingentRuleEntity $contingentRuleEntity
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveUsedRecordException
     * @throws \Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException
     * @return ContingentRuleEntity
     */
    public function removeContingentRule(ContingentRuleEntity $contingentRuleEntity): ContingentRuleEntity
    {
        if ($contingentRuleEntity->isNew()) {
            throw new CanNotRemoveExistingRecordException('The Contingent rule provided does not exist');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            ['id' => $contingentRuleEntity->id]
        );

        $contingentRuleEntity->id = null;

        return $contingentRuleEntity;
    }

    /**
     * @return string query alias for filter construction
     */
    public function getMainTableAlias(): string
    {
        return self::TABLE_ALIAS;
    }

    /**
     * @param int $contingentGroupId
     * @param OwnershipContext $ownershipContext
     * @return bool
     */
    public function isContingentGroupAllowedForOwner(int $contingentGroupId, OwnershipContext $ownershipContext): bool
    {
        return (bool) $this->connection->fetchColumn(
            'SELECT * FROM b2b_contingent_group
             WHERE context_owner_id = :contextOwnerId
             AND id = :contingentGroupId',
            [
                ':contextOwnerId' => $ownershipContext->contextOwnerId,
                ':contingentGroupId' => $contingentGroupId,
            ]
        );
    }

    /**
     * not implemented yet
     *
     * @return string[]
     */
    public function getFullTextSearchFields(): array
    {
        return [];
    }

    /**
     * @param string $ruleType
     * @param int $contingentGroup
     * @param CurrencyContext $currencyContext
     * @return ContingentRuleEntity[]
     */
    public function fetchActiveRuleItemsForRuleType(
        string $ruleType,
        int $contingentGroup,
        CurrencyContext $currencyContext
    ): array {
        $typedRepository = $this->entityFactory
            ->findTypeByName($ruleType)
            ->getRepository($this->connection);

        $typedSubQuery = $typedRepository->createSubQuery();

        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from('(' . $typedSubQuery . ')', $ruleType)
            ->innerJoin(
                $ruleType,
                self::TABLE_NAME,
                self::TABLE_ALIAS,
                self::TABLE_ALIAS . '.id = ' . $ruleType . '.contingent_rule_id'
            )
            ->where(self::TABLE_ALIAS . '.contingent_group_id = :id AND ' . self::TABLE_ALIAS . '.type = :ruleType')
            ->setParameter(':id', $contingentGroup)
            ->setParameter(':ruleType', $ruleType);

        $typedRepository->addSelect($query, $ruleType);

        $entities = [];
        foreach ($query->execute()->fetchAll() as $contingentRuleData) {
            $entity = $this->entityFactory
                ->createEntityFromTypeName($ruleType)
                ->fromDatabaseArrayPrefixed($contingentRuleData);

            if ($entity instanceof ProductNameAware) {
                $this->productNameService->translateProductName($entity);
            }

            if ($entity instanceof CurrencyAware) {
                $this->currencyCalculator
                    ->recalculateAmount($entity, $currencyContext);
            }

            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param OwnershipContext $ownershipContext
     */
    protected function filterContextOwner(QueryBuilder $queryBuilder, OwnershipContext $ownershipContext)
    {
        $queryBuilder->innerJoin(self::TABLE_ALIAS, 'b2b_contingent_group', 'contingentGroup', self::TABLE_ALIAS . '.contingent_group_id = contingentGroup.id')
            ->andWhere('contingentGroup.context_owner_id = :contextOwner')
            ->setParameter('contextOwner', $ownershipContext->contextOwnerId);
    }

    /**
     * @internal
     * @param ContingentRuleEntity $contingentRuleEntity
     * @return ContingentRuleEntity[]
     */
    protected function splitBaseAndExtendedTableData(ContingentRuleEntity $contingentRuleEntity): array
    {
        $data = $contingentRuleEntity->toDatabaseArray();
        $baseTableData = [];
        $extendedTableData = [];

        foreach ($data as $fieldName => $value) {
            if (false !== in_array($fieldName, self::$mainTableFields, true)) {
                $baseTableData[$fieldName] = $value;
                continue;
            }

            $extendedTableData[$fieldName] = $value;
        }

        return [$baseTableData, $extendedTableData];
    }

    /**
     * @internal
     * @param $contingentRuleEntity
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function extractExtendedTableName(ContingentRuleEntity $contingentRuleEntity): string
    {
        return $this->entityFactory
            ->findTypeByName($contingentRuleEntity->type)
            ->getRepository($this->connection)
            ->getTableName();
    }

    /**
     * @internal
     * @param $query
     * @param array $types
     */
    protected function applyTypeQueries(array $types, QueryBuilder $query)
    {
        foreach ($types as $type) {
            $typeRepository = $this->entityFactory
                ->findTypeByName($type)
                ->getRepository($this->connection);

            $query->leftJoin(
                self::TABLE_ALIAS,
                '(' . $typeRepository->createSubQuery() . ')',
                $type,
                self::TABLE_ALIAS . '.id = ' . $type . '.contingent_rule_id'
            );

            $typeRepository->addSelect($query, $type);
        }
    }

    /**
     * @internal
     * @param ContingentRuleEntity $entity
     * @return bool
     */
    protected function isEqualTypeInDatabase(ContingentRuleEntity $entity): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.type')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->setParameter('id', $entity->id);

        return $entity->type === $query->execute()->fetchColumn();
    }

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array
    {
        return [];
    }
}
