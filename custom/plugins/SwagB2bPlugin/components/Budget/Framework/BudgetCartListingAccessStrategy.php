<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Shopware\B2B\Cart\Framework\CartAccessContext;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class BudgetCartListingAccessStrategy implements CartAccessStrategyInterface
{
    /**
     * @var OwnershipContext
     */
    private $ownershipContext;

    /**
     * @var BudgetService
     */
    private $budgetService;

    /**
     * @var CurrencyContext
     */
    private $currencyContext;

    /**
     * @param BudgetService $budgetService
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     */
    public function __construct(
        BudgetService $budgetService,
        OwnershipContext $ownershipContext,
        CurrencyContext $currencyContext
    ) {
        $this->ownershipContext = $ownershipContext;
        $this->budgetService = $budgetService;
        $this->currencyContext = $currencyContext;
    }

    /**
     * @param CartAccessContext $context
     * @param CartAccessResult $cartAccessResult
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult)
    {
        $amount = (float) $context->orderClearanceEntity->list->amountNet;

        $hasBudget = $this->budgetService
            ->hasBudgetWithAtLeastRemainingAmount($this->ownershipContext, $amount, $this->currencyContext);

        if ($hasBudget) {
            return;
        }

        $cartAccessResult->setClearable(false);
    }

    /**
     * {@inheritdoc}
     */
    public function addInformation(CartAccessResult $cartAccessResult)
    {
        //nth
    }
}
