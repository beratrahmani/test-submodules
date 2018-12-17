<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\B2B\Acl\Framework\AclReadHelper;
use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotRemoveExistingRecordException;
use Shopware\B2B\Common\Repository\CanNotUpdateExistingRecordException;
use Shopware\B2B\Common\Repository\DbalHelper;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Company\Framework\CompanyFilterHelper;
use Shopware\B2B\Currency\Framework\CurrencyCalculator;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\Shop\Framework\SessionStorageInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class BudgetRepository implements GridRepository
{
    const TABLE_NAME = 'b2b_budget';

    const TABLE_ALIAS = 'budget';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DbalHelper
     */
    private $dbalHelper;

    /**
     * @var SessionStorageInterface
     */
    private $cartStorage;

    /**
     * @var CurrencyCalculator
     */
    private $currencyCalculator;

    /**
     * @var AclReadHelper
     */
    private $aclReadHelper;

    /**
     * @var CompanyFilterHelper
     */
    private $companyFilterHelper;

    /**
     * @var BudgetCompanyAssignmentFilter
     */
    private $budgetCompanyAssignmentFilter;

    /**
     * @var BudgetCompanyInheritanceFilter
     */
    private $budgetCompanyInheritanceFilter;

    /**
     * @param Connection $connection
     * @param DbalHelper $dbalHelper
     * @param SessionStorageInterface $cartStorage
     * @param CurrencyCalculator $currencyCalculator
     * @param AclReadHelper $aclReadHelper
     * @param CompanyFilterHelper $companyFilterHelper
     * @param BudgetCompanyAssignmentFilter $budgetCompanyAssignmentFilter
     * @param BudgetCompanyInheritanceFilter $budgetCompanyInheritanceFilter
     */
    public function __construct(
        Connection $connection,
        DbalHelper $dbalHelper,
        SessionStorageInterface $cartStorage,
        CurrencyCalculator $currencyCalculator,
        AclReadHelper $aclReadHelper,
        CompanyFilterHelper $companyFilterHelper,
        BudgetCompanyAssignmentFilter $budgetCompanyAssignmentFilter,
        BudgetCompanyInheritanceFilter $budgetCompanyInheritanceFilter
    ) {
        $this->connection = $connection;
        $this->dbalHelper = $dbalHelper;
        $this->cartStorage = $cartStorage;
        $this->currencyCalculator = $currencyCalculator;
        $this->aclReadHelper = $aclReadHelper;
        $this->companyFilterHelper = $companyFilterHelper;
        $this->budgetCompanyAssignmentFilter = $budgetCompanyAssignmentFilter;
        $this->budgetCompanyInheritanceFilter = $budgetCompanyInheritanceFilter;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param BudgetSearchStruct $searchStruct
     * @param CurrencyContext $currencyContext
     * @return BudgetEntity[]
     */
    public function fetchList(
        OwnershipContext $ownershipContext,
        BudgetSearchStruct $searchStruct,
        CurrencyContext $currencyContext
    ): array {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS);

        $this->filterByContextOwner($ownershipContext, $query);
        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $this->companyFilterHelper
            ->applyFilter(
                $this->aclReadHelper,
                $this->budgetCompanyAssignmentFilter,
                $searchStruct,
                $query,
                $this->budgetCompanyInheritanceFilter
            );

        if (!$searchStruct->orderBy) {
            $searchStruct->orderBy = self::TABLE_ALIAS . '.id';
            $searchStruct->orderDirection = 'DESC';
        }

        $this->dbalHelper->applySearchStruct($searchStruct, $query);

        $statement = $query->execute();
        $rawBudgets = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $budgets = [];
        foreach ($rawBudgets as $rawBudget) {
            $budgets[] = (new BudgetEntity())->fromDatabaseArray($rawBudget);
        }

        $this->currencyCalculator->recalculateAmounts($budgets, $currencyContext);

        return $budgets;
    }

    /**
     * @param OwnershipContext $context
     * @param BudgetSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(
        OwnershipContext $context,
        BudgetSearchStruct $searchStruct
    ): int {
        $query = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS);

        $this->filterByContextOwner($context, $query);
        $this->aclReadHelper->applyAclVisibility($context, $query);

        $this->companyFilterHelper
            ->applyFilter(
                $this->aclReadHelper,
                $this->budgetCompanyAssignmentFilter,
                $searchStruct,
                $query,
                $this->budgetCompanyInheritanceFilter
            );

        $this->dbalHelper->applyFilters($searchStruct, $query);

        $statement = $query->execute();

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @param CurrencyContext $currencyContext
     * @return BudgetEntity[]
     */
    public function fetchAllBudgets(CurrencyContext $currencyContext): array
    {
        $rawBudgets = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $budgets = [];
        foreach ($rawBudgets as $rawBudget) {
            $budgets[] = (new BudgetEntity())->fromDatabaseArray($rawBudget);
        }

        $this->currencyCalculator->recalculateAmounts($budgets, $currencyContext);

        return $budgets;
    }

    /**
     * @param int $budgetId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return BudgetEntity
     */
    public function fetchOneById(
        int $budgetId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): BudgetEntity {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id=:id')
            ->setParameter('id', $budgetId);

        $this->filterByContextOwner($ownershipContext, $query);
        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $statement = $query->execute();
        $rawBudget = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$rawBudget) {
            $rawBudget = $this->fetchOneByIdAndOwnerId($budgetId, $ownershipContext);
        }

        $budget = new BudgetEntity();
        $budget->fromDatabaseArray($rawBudget);
        $this->currencyCalculator->recalculateAmount($budget, $currencyContext);

        return $budget;
    }

    /**
     * @internal
     * @param int $budgetId
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return array
     */
    protected function fetchOneByIdAndOwnerId(int $budgetId, OwnershipContext $ownershipContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select(self::TABLE_ALIAS . '.*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id = :id')
            ->andWhere(self::TABLE_ALIAS . '.owner_id = :ownerId')
            ->setParameter('id', $budgetId)
            ->setParameter('ownerId', $ownershipContext->authId);

        $this->filterByContextOwner($ownershipContext, $query);

        $rawBudget = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$rawBudget) {
            throw new NotFoundException('Unable to select a budget with id "' . $budgetId . '"');
        }

        return $rawBudget;
    }

    /**
     * @param int $budgetId
     * @param OwnershipContext $ownershipContext
     * @return bool
     */
    public function hasBudget(int $budgetId, OwnershipContext $ownershipContext): bool
    {
        return (bool) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.id=:id')
            ->andWhere(self::TABLE_ALIAS . '.context_owner_id = :contextOwner')
            ->setParameter('id', $budgetId)
            ->setParameter('contextOwner', $ownershipContext->contextOwnerId)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param BudgetEntity $budget
     * @param OwnershipContext $ownershipContext
     * @throws CanNotInsertExistingRecordException
     * @return BudgetEntity
     */
    public function addBudget(BudgetEntity $budget, OwnershipContext $ownershipContext): BudgetEntity
    {
        if (!$budget->isNew()) {
            throw new CanNotInsertExistingRecordException('The budget provided already exists');
        }

        $this->connection->insert(
            self::TABLE_NAME,
            array_merge(
                $budget->toDatabaseArray(),
                ['context_owner_id' => $ownershipContext->contextOwnerId]
            )
        );

        $budget->id = (int) $this->connection->lastInsertId();

        return $budget;
    }

    /**
     * @param BudgetEntity $budget
     * @param OwnershipContext $ownershipContext
     * @throws CanNotUpdateExistingRecordException
     * @return BudgetEntity
     */
    public function updateBudget(BudgetEntity $budget, OwnershipContext $ownershipContext): BudgetEntity
    {
        if ($budget->isNew()) {
            throw new CanNotUpdateExistingRecordException('The budget provided does not exist');
        }

        $this->connection->update(
            self::TABLE_NAME,
            $budget->toDatabaseArray(),
            [
                'id' => $budget->id,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );

        return $budget;
    }

    /**
     * @param BudgetEntity $budget
     * @param OwnershipContext $ownershipContext
     * @throws CanNotRemoveExistingRecordException
     * @return BudgetEntity
     */
    public function removeBudget(BudgetEntity $budget, OwnershipContext $ownershipContext): BudgetEntity
    {
        if ($budget->isNew()) {
            throw new CanNotRemoveExistingRecordException('The budget provided does not exist');
        }

        $this->connection->delete(
            self::TABLE_NAME,
            [
                'id' => $budget->id,
                'context_owner_id' => $ownershipContext->contextOwnerId,
            ]
        );

        $budget->id = null;

        return $budget;
    }

    /**
     * @param int $budgetId
     * @param int $authId
     * @param int $orderContextId
     * @param int $refreshGroup
     * @param float $amount
     * @param CurrencyContext $currencyContext
     * @return int
     */
    public function addTransaction(
        int $budgetId,
        int $authId,
        int $orderContextId,
        int $refreshGroup,
        float $amount,
        CurrencyContext $currencyContext
    ): int {
        $this->connection->insert(
            'b2b_budget_transaction',
            [
                'budget_id' => $budgetId,
                'auth_id' => $authId,
                'order_context_id' => $orderContextId,
                'refresh_group' => $refreshGroup,
                'amount' => $amount,
                'currency_factor' => $currencyContext->currentCurrencyFactor,
            ]
        );

        return (int) $this->connection->lastInsertId();
    }

    /**
     * @param int $budgetId
     * @param int $refreshGroup
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return BudgetStatus
     */
    public function fetchOneStatusByBudgetIdInGroup(
        int $budgetId,
        int $refreshGroup,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): BudgetStatus {
        $transactionSnippet = $this->currencyCalculator
            ->getSqlCalculationPart('amount', 'currency_factor', $currencyContext);

        $budgetSnippet = $this->currencyCalculator
            ->getSqlCalculationPart('amount', 'currency_factor', $currencyContext, 'budget');

        $rawData = $this->connection->fetchAssoc(
            'SELECT 
              IF(transactionSum.used_budget, transactionSum.used_budget, 0) AS used_budget, 
              ' . $budgetSnippet . ' AS available_budget , 
              (' . $budgetSnippet . ' - IF(transactionSum.used_budget, transactionSum.used_budget, 0)) as remaining_budget,
              budget.currency_factor
            FROM b2b_budget budget
            LEFT JOIN (SELECT 
                  SUM(' . $transactionSnippet . ') AS used_budget,
                  budget_id
                FROM b2b_budget_transaction 
                WHERE budget_id=:budgetId AND refresh_group=:refreshGroup AND active=1
                GROUP BY budget_id) AS transactionSum ON budget.id = transactionSum.budget_id
            WHERE id=:budgetId AND context_owner_id = :contextOwner',
            [
                'budgetId' => $budgetId,
                'refreshGroup' => $refreshGroup,
                'contextOwner' => $ownershipContext->contextOwnerId,
            ]
        );

        $budgetStatus = new BudgetStatus();
        $budgetStatus->fromDataBaseArray($rawData);

        return $budgetStatus;
    }

    /**
     * @param \Closure $logicToExecute
     */
    public function executeTransactional(\Closure $logicToExecute)
    {
        $this->connection->transactional($logicToExecute);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return BudgetEntity[]
     */
    public function fetchDirectlyAssignedBudgets(OwnershipContext $ownershipContext, CurrencyContext $currencyContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.owner_id = :ownerId AND active=1')
            ->orderBy('name')
            ->setParameter('ownerId', $ownershipContext->authId);

        $this->filterByContextOwner($ownershipContext, $query);

        $statement = $query->execute();
        $rawBudgets = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$rawBudgets) {
            throw new NotFoundException(sprintf('No budgets found for %s', $ownershipContext->authId));
        }

        $budgets = [];
        foreach ($rawBudgets as $rawBudget) {
            $budgets[] = (new BudgetEntity())->fromDatabaseArray($rawBudget);
        }

        $this->currencyCalculator->recalculateAmounts($budgets, $currencyContext);

        return $budgets;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return BudgetEntity[]
     */
    public function fetchAllowedBudgets(OwnershipContext $ownershipContext, CurrencyContext $currencyContext): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, self::TABLE_ALIAS)
            ->where(self::TABLE_ALIAS . '.active=1')
            ->orderBy('name');

        $this->filterByContextOwner($ownershipContext, $query);
        $this->aclReadHelper->applyAclVisibility($ownershipContext, $query);

        $statement = $query->execute();
        $rawBudgets = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$rawBudgets) {
            throw new NotFoundException(sprintf('No budgets found for %s', $ownershipContext->authId));
        }

        $budgets = [];
        foreach ($rawBudgets as $rawBudget) {
            $budgets[] = (new BudgetEntity())->fromDatabaseArray($rawBudget);
        }

        $this->currencyCalculator->recalculateAmounts($budgets, $currencyContext);

        return $budgets;
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
            'identifier',
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
     *
     * @param OwnershipContext $ownershipContext
     * @param QueryBuilder $query
     */
    protected function filterByContextOwner(OwnershipContext $ownershipContext, QueryBuilder $query)
    {
        $query->andWhere(self::TABLE_ALIAS . '.context_owner_id = :contextOwnerId')
            ->setParameter('contextOwnerId', $ownershipContext->contextOwnerId);
    }

    /**
     * @param int $orderContextId
     * @param int $budgetId
     */
    public function setOrderBudgetPreferenceByOrderContextId(int $orderContextId, int $budgetId)
    {
        $this->connection->update(
            'b2b_order_context',
            ['budget_id ' => $budgetId],
            ['id' => $orderContextId]
        );
    }

    /**
     * @param int $budgetId
     */
    public function setOrderBudgetPreferenceByCart(int $budgetId)
    {
        $this->cartStorage->set('b2bBudgetId', $budgetId);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @return int
     */
    public function fetchOrderBudgetPreferenceByCart(OwnershipContext $ownershipContext): int
    {
        $budgetId = (int) $this->cartStorage->get('b2bBudgetId');

        if (!$budgetId) {
            throw new NotFoundException('Unable to load budget id, none stored');
        }

        if (!$this->hasBudget($budgetId, $ownershipContext)) {
            throw new NotFoundException('Unable to load budget id, valid id stored');
        }

        return $budgetId;
    }

    /**
     * @param int $orderContextId
     * @param OwnershipContext $ownershipContext
     * @throws NotFoundException
     * @return int
     */
    public function fetchBudgetIdByOrderContextId(int $orderContextId, OwnershipContext $ownershipContext): int
    {
        $budgetId = $this->connection->fetchColumn(
            'SELECT budget_id FROM b2b_order_context WHERE id=:orderContextId
              and auth_id in (SELECT id from b2b_store_front_auth where auth_id = :contextOwner or context_owner_id = :contextOwner)',
            [
                'orderContextId' => $orderContextId,
                'contextOwner' => $ownershipContext->contextOwnerId,
            ]
        );

        if (!$budgetId) {
            throw new NotFoundException('Unable to find a budget by order context id');
        }

        return (int) $budgetId;
    }

    /**
     * @param int $orderContextId
     */
    public function setTransactionInactive(int $orderContextId)
    {
        $this->updateTransaction($orderContextId, 0);
    }

    /**
     * @param int $orderContextId
     */
    public function setTransactionActive(int $orderContextId)
    {
        $this->updateTransaction($orderContextId, 1);
    }

    /**
     * @internal
     * @param int $orderContextId
     * @param int $active
     */
    protected function updateTransaction(int $orderContextId, int $active)
    {
        $this->connection->update(
            'b2b_budget_transaction',
            ['active' => $active],
            ['order_context_id' => $orderContextId]
        );
    }
}
