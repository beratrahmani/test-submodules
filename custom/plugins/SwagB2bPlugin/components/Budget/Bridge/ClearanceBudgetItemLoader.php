<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Bridge;

use Shopware\B2B\Budget\Framework\BudgetRepository;
use Shopware\B2B\Budget\Framework\BudgetService;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\OrderClearance\Bridge\OrderItemLoaderInterface;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class ClearanceBudgetItemLoader implements OrderItemLoaderInterface
{
    /**
     * @var BudgetRepository
     */
    private $budgetRepository;

    /**
     * @var BudgetService
     */
    private $budgetService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param BudgetRepository $budgetRepository
     * @param BudgetService $budgetService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        BudgetRepository $budgetRepository,
        BudgetService $budgetService,
        CurrencyService $currencyService
    ) {
        $this->budgetRepository = $budgetRepository;
        $this->budgetService = $budgetService;
        $this->currencyService = $currencyService;
    }

    /**
     * ·{@inheritdoc}
     */
    public function fetchItemsFromStorage(OrderClearanceEntity $itemEntity, OwnershipContext $ownershipContext): array
    {
        try {
            $budgetId = $this->budgetRepository
                ->fetchBudgetIdByOrderContextId($itemEntity->id, $ownershipContext);
        } catch (NotFoundException $e) {
            return [];
        }

        $budgetStatus = $this->budgetService->getBudgetStatus(
            $budgetId,
            $this->currencyService->createCurrencyContext(),
            $ownershipContext
        );

        return [$budgetStatus];
    }

    /**
     * ·{@inheritdoc}
     */
    public function fetchItemsFromBasketArray(
        OrderClearanceEntity $itemEntity,
        array $basketArray,
        OwnershipContext $ownershipContext
    ): array {
        try {
            $budgetId = $this->budgetRepository
                ->fetchOrderBudgetPreferenceByCart($ownershipContext);

            return [$this->budgetService->getBudgetStatus($budgetId, $this->currencyService->createCurrencyContext(), $ownershipContext)];
        } catch (NotFoundException $e) {
            return [];
        }
    }
}
