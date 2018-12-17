<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Budget\Framework\BudgetRepository;
use Shopware\B2B\Budget\Framework\BudgetService;
use Shopware\B2B\Cart\Bridge\CartAccessSubscriber;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class BudgetPreferenceSubscriber implements SubscriberInterface
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

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
     * @param AuthenticationService $authenticationService
     * @param BudgetRepository $budgetRepository
     * @param BudgetService $budgetService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        BudgetRepository $budgetRepository,
        BudgetService $budgetService,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->budgetRepository = $budgetRepository;
        $this->budgetService = $budgetService;
        $this->currencyService = $currencyService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'updateCartPreference',
            CartAccessSubscriber::EVENT_NAME => 'addBudgetPreference',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function updateCartPreference(\Enlight_Event_EventArgs $args)
    {
        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            $this->budgetRepository
                ->fetchOrderBudgetPreferenceByCart($ownershipContext);

            return;
        } catch (NotFoundException $e) {
            //nth
        }

        $amount = (float) Shopware()->Modules()->Basket()->sGetBasket()['AmountNetNumeric'];
        $context = $this->authenticationService->getIdentity()->getOwnershipContext();

        $budgetId = $this->findPreferredBudgetId($context, $amount);

        $this->budgetRepository
            ->setOrderBudgetPreferenceByCart($budgetId);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addBudgetPreference(\Enlight_Event_EventArgs $args)
    {
        /** @var OrderContext $orderContext */
        $orderContext = $args->get('orderContext');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            $budgetId = $this->budgetRepository
                ->fetchOrderBudgetPreferenceByCart($ownershipContext);
        } catch (NotFoundException $e) {
            return;
        }

        $this->budgetRepository
            ->setOrderBudgetPreferenceByOrderContextId($orderContext->id, $budgetId);

        $this->budgetRepository
            ->setOrderBudgetPreferenceByCart(0);
    }

    /**
     * @param OwnershipContext $context
     * @param float $amount
     * @return int
     */
    public function findPreferredBudgetId(OwnershipContext $context, float $amount): int
    {
        $currencyContext = $this->currencyService->createCurrencyContext();

        $budgets = $this->budgetService
            ->getUserSelectableBudgetsWithStatus($context, $amount, $currencyContext);

        $budgetId = 0;
        foreach ($budgets as $budget) {
            if ($budget->currentStatus->isSufficient) {
                $budgetId = $budget->id;
                break;
            }
        }

        return $budgetId;
    }
}
