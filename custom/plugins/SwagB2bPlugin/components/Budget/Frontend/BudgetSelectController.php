<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Frontend;

use Shopware\B2B\Budget\Framework\BudgetEntity;
use Shopware\B2B\Budget\Framework\BudgetRepository;
use Shopware\B2B\Budget\Framework\BudgetService;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class BudgetSelectController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var BudgetService
     */
    private $budgetService;

    /**
     * @var BudgetRepository
     */
    private $budgetRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param AuthenticationService $authenticationService
     * @param BudgetService $budgetService
     * @param BudgetRepository $budgetRepository
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        BudgetService $budgetService,
        BudgetRepository $budgetRepository,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->budgetService = $budgetService;
        $this->budgetRepository = $budgetRepository;
        $this->currencyService = $currencyService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $amount = (float) $request
            ->requireParam('amount');

        return $this->createIndexResponse($amount);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function selectAction(Request $request): array
    {
        $request->checkPost();

        $amount = (float) $request
            ->requireParam('amount');

        $budgetId = (int) $request
            ->requireParam('b2bBudgetReference');

        $this->budgetRepository
            ->setOrderBudgetPreferenceByCart($budgetId);

        return $this->createIndexResponse($amount);
    }

    /**
     * @internal
     * @param BudgetEntity[] $availableBudgets
     * @param OwnershipContext $ownershipContext
     * @return BudgetEntity|null
     */
    protected function getPreselectedBudget(array $availableBudgets, OwnershipContext $ownershipContext)
    {
        if (!$availableBudgets) {
            return;
        }

        try {
            $budgetId = $this->budgetRepository
                ->fetchOrderBudgetPreferenceByCart($ownershipContext);
        } catch (NotFoundException $e) {
            return;
        }

        foreach ($availableBudgets as $budget) {
            if ($budget->id === $budgetId && $budget->currentStatus->isSufficient) {
                return $budget;
            }
        }
        foreach ($availableBudgets as $budget) {
            if ($budget->currentStatus->isSufficient) {
                return $budget;
            }
        }
    }

    /**
     * @internal
     * @param float $amount
     * @return array
     */
    protected function createIndexResponse(float $amount): array
    {
        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $budgets = $this->budgetService
            ->getUserSelectableBudgetsWithStatus($ownershipContext, $amount, $currencyContext);

        return [
            'b2bBudgets' => $budgets,
            'b2bSelectedBudget' => $this->getPreselectedBudget($budgets, $ownershipContext),
            'amount' => $amount,
        ];
    }
}
