<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Shopware\B2B\Cart\Framework\CartAccessFactoryInterface;
use Shopware\B2B\Cart\Framework\CartAccessStrategyAlwaysAllowed;
use Shopware\B2B\Cart\Framework\CartAccessStrategyInterface;
use Shopware\B2B\Cart\Framework\CartService;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class BudgetCartAccessFactory implements CartAccessFactoryInterface
{
    /**
     * @var BudgetService
     */
    private $budgetService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param BudgetService $budgetService
     * @param CurrencyService $currencyService
     */
    public function __construct(BudgetService $budgetService, CurrencyService $currencyService)
    {
        $this->budgetService = $budgetService;
        $this->currencyService = $currencyService;
    }

    /**
     * @param Identity $identity
     * @param string $environmentName
     * @return CartAccessStrategyInterface
     */
    public function createCartAccessForIdentity(Identity $identity, string $environmentName): CartAccessStrategyInterface
    {
        switch ($environmentName) {
            case CartService::ENVIRONMENT_NAME_ORDER:
                return new BudgetCartOrderAccessStrategy($identity, $this->budgetService, $this->currencyService->createCurrencyContext());
            case CartService::ENVIRONMENT_NAME_LISTING:
                return new BudgetCartListingAccessStrategy($this->budgetService, $identity->getOwnershipContext(), $this->currencyService->createCurrencyContext());
            case CartService::ENVIRONMENT_NAME_MODIFY:
            default:
                return new CartAccessStrategyAlwaysAllowed();
        }
    }
}
