<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Budget\Framework\BudgetService;
use Shopware\B2B\Budget\Framework\InsufficientBudgetException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Order\Bridge\OrderChangeTrigger;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class BudgetTransactionOrderSync implements SubscriberInterface
{
    /**
     * @var BudgetService
     */
    private $budgetService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param AuthenticationService $authenticationService
     * @param OrderContextRepository $orderContextRepository
     * @param BudgetService $budgetService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderContextRepository $orderContextRepository,
        BudgetService $budgetService,
        CurrencyService $currencyService
    ) {
        $this->budgetService = $budgetService;
        $this->authenticationService = $authenticationService;
        $this->orderContextRepository = $orderContextRepository;
        $this->currencyService = $currencyService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Controllers_Frontend_Checkout::saveOrder::after' => 'addTransaction',
            OrderChangeTrigger::EVENT_NAME => 'updateTransactionStatus',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function addTransaction(\Enlight_Hook_HookArgs $args)
    {
        $orderNumber = $args->getReturn();
        $args->setReturn($orderNumber);

        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $currencyContext = $this->currencyService->createCurrencyContext();

        $identity = $this->authenticationService->getIdentity();
        $amount = Shopware()->Modules()->Order()->sBasketData['AmountNetNumeric'];

        $orderContext = $this->orderContextRepository
            ->fetchOneOrderContextByOrderNumber((string) $orderNumber);

        try {
            $this->budgetService
                ->addTransaction($orderContext, $amount, $currencyContext, $identity->getOwnershipContext());
        } catch (InsufficientBudgetException $e) {
            if (!$identity->isSuperAdmin()) {
                throw $e;
            }
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function updateTransactionStatus(\Enlight_Event_EventArgs $args)
    {
        /** @var OrderContext $orderContext */
        $orderContext = $args->get('orderContext');

        $this->budgetService->updateTransactionStatus($orderContext);
    }
}
