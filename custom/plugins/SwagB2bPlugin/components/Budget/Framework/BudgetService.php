<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Shopware\B2B\Common\Repository\MysqlRepository;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderStatusInterpreterServiceInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class BudgetService
{
    const TYPE_NONE = 'none';

    const TYPE_MONTHLY = 'monthly';

    const TYPE_YEARLY = 'yearly';

    const TYPE_QUARTERLY = 'quarterly';

    const TYPE_BIANNUAL = 'biannual';

    /**
     * @var BudgetRepository
     */
    private $budgetRepository;

    /**
     * @var BudgetNotificationRepository
     */
    private $notificationRepository;

    /**
     * @var OrderStatusInterpreterServiceInterface
     */
    private $orderStatusInterpreterService;

    /**
     * @param BudgetRepository $budgetRepository
     * @param BudgetNotificationRepository $notificationRepository
     * @param OrderStatusInterpreterServiceInterface $orderStatusInterpreterService
     */
    public function __construct(
        BudgetRepository $budgetRepository,
        BudgetNotificationRepository $notificationRepository,
        OrderStatusInterpreterServiceInterface $orderStatusInterpreterService
    ) {
        $this->budgetRepository = $budgetRepository;
        $this->notificationRepository = $notificationRepository;
        $this->orderStatusInterpreterService = $orderStatusInterpreterService;
    }

    /**
     * @param OrderContext $orderContext
     * @param float $amount
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @throws InsufficientBudgetException
     */
    public function addTransaction(
        OrderContext $orderContext,
        float $amount,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ) {
        try {
            $budgetId = $this->budgetRepository
                ->fetchBudgetIdByOrderContextId($orderContext->id, $ownershipContext);
        } catch (NotFoundException $e) {
            return;
        }

        $date = new \DateTime();
        $refreshGroup = $this->getRefreshGroup($budgetId, $date, $currencyContext, $ownershipContext);
        $status = $this->getBudgetStatus($budgetId, $currencyContext, $ownershipContext, $date);

        if ($status->remainingBudget < $amount) {
            throw new InsufficientBudgetException(
                'Unable to book amount "' . $amount . '" on budget "' . $budgetId . '"'
            );
        }

        $this->budgetRepository->executeTransactional(function () use ($budgetId, $orderContext, $amount, $refreshGroup, $currencyContext) {
            $this->budgetRepository->addTransaction(
                $budgetId,
                $orderContext->authId,
                $orderContext->id,
                $refreshGroup,
                $amount,
                $currencyContext
            );

            $this->updateTransactionStatus($orderContext);
        });
    }

    /**
     * @param OrderContext $orderContext
     */
    public function updateTransactionStatus(OrderContext $orderContext)
    {
        if ($this->orderStatusInterpreterService->isOpen($orderContext->statusId)) {
            $this->budgetRepository->setTransactionActive($orderContext->id);

            return;
        }

        $this->budgetRepository->setTransactionInactive($orderContext->id);
    }

    /**
     * @param int $budgetId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @param \DateTime|null $onDate
     * @return BudgetStatus
     */
    public function getBudgetStatus(
        int $budgetId,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext,
        \DateTime $onDate = null
    ): BudgetStatus {
        if (!$onDate) {
            $onDate = new \DateTime();
        }

        $refreshGroup = $this->getRefreshGroup($budgetId, $onDate, $currencyContext, $ownershipContext);

        $status = $this->budgetRepository->fetchOneStatusByBudgetIdInGroup($budgetId, $refreshGroup, $currencyContext, $ownershipContext);

        return $status;
    }

    /**
     * @param OwnershipContext $context
     * @param float $amount
     * @param CurrencyContext $currencyContext
     * @return bool
     */
    public function hasBudgetWithAtLeastRemainingAmount(OwnershipContext $context, float $amount, CurrencyContext $currencyContext): bool
    {
        $budgets = $this->getUserSelectableBudgetsWithStatus($context, $amount, $currencyContext);

        foreach ($budgets as $budget) {
            $status = $budget->currentStatus;

            if ($status->isSufficient) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param float $againstAmount
     * @param CurrencyContext $currencyContext
     * @return BudgetEntity[]
     */
    public function getUserSelectableBudgetsWithStatus(
        OwnershipContext $ownershipContext,
        float $againstAmount,
        CurrencyContext $currencyContext
    ): array {
        try {
            $budgetsAssigned = $this->budgetRepository
                ->fetchDirectlyAssignedBudgets($ownershipContext, $currencyContext);
        } catch (NotFoundException $e) {
            $budgetsAssigned = [];
        }

        try {
            $budgetsAllowed = $this->budgetRepository->fetchAllowedBudgets($ownershipContext, $currencyContext);
        } catch (NotFoundException $e) {
            $budgetsAllowed = [];
        }

        $result = array_merge($budgetsAllowed, $budgetsAssigned);
        $budgets = array_map('unserialize', array_unique(array_map('serialize', $result)));

        return $this->extendBudgetsWithStatus($budgets, $againstAmount, $currencyContext, $ownershipContext);
    }

    /**
     * @param BudgetEntity $budget
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    public function prepareMail(
        BudgetEntity $budget,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): array {
        if (!$budget->notifyAuthor || $budget->notifyAuthorPercentage <= 0 || $budget->ownerId <= 0) {
            return [];
        }

        $refreshGroup = $this->getRefreshGroup($budget->id, new \DateTime(), $currencyContext, $ownershipContext);

        try {
            $this->notificationRepository->fetchNotifyByIdAndRefreshGroup($budget->id, $refreshGroup);
        } catch (NotFoundException $e) {
            return $this->getMailVariables($budget, $refreshGroup, $currencyContext, $ownershipContext);
        }

        return [];
    }

    /**
     * @internal
     * @param BudgetEntity $budget
     * @param int $refreshGroup
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    protected function getMailVariables(
        BudgetEntity $budget,
        int $refreshGroup,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): array {
        $budgetStatus = $this->getBudgetStatus($budget->id, $currencyContext, $ownershipContext);

        $percentageUsed = (100 / $budgetStatus->availableBudget) * $budgetStatus->usedBudget;

        if ($percentageUsed < $budget->notifyAuthorPercentage) {
            return [];
        }

        return [
            'budgetStatus' => $budgetStatus->toArray(),
            'budget' => $budget->toArray(),
            'refreshGroup' => $refreshGroup,
        ];
    }

    /**
     * @param int $budgetId
     * @param \DateTime $dateTime
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @throws \DomainException
     * @return int
     */
    public function getRefreshGroup(
        int $budgetId,
        \DateTime $dateTime,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): int {
        $budget = $this->budgetRepository->fetchOneById($budgetId, $currencyContext, $ownershipContext);

        $date = clone $dateTime;

        if ($budget->fiscalYear) {
            $fiscalYear = \DateTime::createFromFormat(MysqlRepository::MYSQL_DATE_FORMAT, $budget->fiscalYear);
            $date->sub(new \DateInterval(sprintf('P%dD', $fiscalYear->format('z'))));
        }

        switch ($budget->refreshType) {
            case self::TYPE_YEARLY:
                return (int) $date->format('Y');
            case self::TYPE_MONTHLY:
                return (int) $date->format('Ym');
            case self::TYPE_QUARTERLY:
                $quarter = (int) ceil(((int) $date->format('n') / 3));
                $quarter = $quarter > 10 ? $quarter : '0' . $quarter;

                return (int) ($date->format('Y') . $quarter);
            case self::TYPE_BIANNUAL:
                $half = '0' . (int) ceil(((int) $date->format('n') / 6));

                return (int) ($date->format('Y') . $half);
            case self::TYPE_NONE:
                return 0;
        }

        throw new \DomainException('Unable to generate refresh group for "' . $budget->refreshType . '"');
    }

    /**
     * @internal
     * @param BudgetEntity[] $budgets
     * @param float $againstAmount
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return BudgetEntity[]
     */
    protected function extendBudgetsWithStatus(
        array $budgets,
        float $againstAmount,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ): array {
        foreach ($budgets as $budget) {
            $budget->currentStatus = $this->getBudgetStatus($budget->id, $currencyContext, $ownershipContext);

            $budget->currentStatus->isSufficient = !($budget->currentStatus->remainingBudget < $againstAmount);
        }

        return $budgets;
    }
}
