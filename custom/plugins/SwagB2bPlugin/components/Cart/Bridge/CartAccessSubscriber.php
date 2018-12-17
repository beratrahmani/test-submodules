<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartService;
use Shopware\B2B\Common\Controller\B2bControllerRoutingException;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Debtor\Framework\DebtorIdentity;
use Shopware\B2B\OrderClearance\Bridge\OrderClearanceEntityFactory;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class CartAccessSubscriber implements SubscriberInterface
{
    const EVENT_NAME = __CLASS__ . '::order-context-created';

    const CHANGE_TO_OLD_STATE = __CLASS__ . '::old-state';

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var CartAccessResult
     */
    private $cartAccessResult;

    /**
     * @var OrderClearanceEntityFactory
     */
    private $entityFactory;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var CartAccessDefaultModeInterface
     */
    private $cartAccessMode;

    /**
     * @var CartAccessModeRegistry
     */
    private $cartAccessModeRegistry;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CartState
     */
    private $cartState;

    /**
     * @param AuthenticationService $authenticationService
     * @param CartService $cartService
     * @param OrderClearanceEntityFactory $entityFactory
     * @param \Enlight_Event_EventManager $eventManager
     * @param CartAccessModeRegistry $cartAccessModeRegistry
     * @param Connection $connection
     * @param CartState $cartState
     * @internal param ModelManager $modelManager
     */
    public function __construct(
        AuthenticationService $authenticationService,
        CartService $cartService,
        OrderClearanceEntityFactory $entityFactory,
        \Enlight_Event_EventManager $eventManager,
        CartAccessModeRegistry $cartAccessModeRegistry,
        Connection $connection,
        CartState $cartState
     ) {
        $this->authenticationService = $authenticationService;
        $this->cartService = $cartService;
        $this->entityFactory = $entityFactory;
        $this->eventManager = $eventManager;
        $this->cartAccessModeRegistry = $cartAccessModeRegistry;
        $this->connection = $connection;
        $this->cartState = $cartState;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend' => [['checkCartState'], ['initializeMode']],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'addTemplateMessagesAndError',
            'sOrder::sSaveOrder::before' => 'handleOrderBeforeCreate',
            'sOrder::sSaveOrder::after' => 'finishOrder',
            'Shopware_Modules_Admin_GetPaymentMeans_DataFilter' => 'onGetPaymentMeans',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return array
     */
    public function onGetPaymentMeans(\Enlight_Event_EventArgs $args)
    {
        $payments = $args->getReturn();

        $clearancePaymentKey = null;
        foreach ($payments as $key => $payment) {
            if ($payment['name'] === 'b2b_order_clearance_payment') {
                $clearancePaymentKey = $key;
            }
        }

        if ($clearancePaymentKey === null) {
            $args->setReturn($payments);

            return $payments;
        }

        if (!$this->authenticationService->isB2b()
            || ($identity = $this->authenticationService->getIdentity()) instanceof DebtorIdentity) {
            unset($payments[$clearancePaymentKey]);

            $args->setReturn($payments);

            return $payments;
        }

        $identity = $this->authenticationService->getIdentity();

        $this->computeOrderAccessibility();

        if ($this->cartAccessResult->hasErrors()) {
            $clearancePayment = [
                $payments[$clearancePaymentKey],
            ];

            $this->setUserPayment($identity, (int) $clearancePayment[0]['id']);

            $args->setReturn($clearancePayment);

            return $clearancePayment;
        }

        unset($payments[$clearancePaymentKey]);

        $args->setReturn($payments);

        return $payments;
    }

    /**
     * @internal
     * @param Identity $identity
     * @param int $paymentId
     */
    protected function setUserPayment(Identity $identity, int $paymentId)
    {
        $this->connection->update(
            's_user',
            ['paymentID' => $paymentId],
            ['email' => $identity->getLoginCredentials()->email]
        );
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function initializeMode(\Enlight_Event_EventArgs $args)
    {
        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $subject = $args->getSubject();

        if (!($subject instanceof \Shopware_Controllers_Frontend_Checkout) && !($subject instanceof \Shopware_Controllers_Frontend_Payment)) {
            return;
        }

        $this->cartAccessMode = $this->cartAccessModeRegistry
            ->getAvailableMode();
        $identity = $this->authenticationService->getIdentity();

        $this->cartAccessMode->enable($identity->getOwnershipContext());

        $args->getSubject()
            ->View()
            ->assign(['b2bCartMode' => $this->cartAccessMode->getName()]);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addTemplateMessagesAndError(\Enlight_Event_EventArgs $args)
    {
        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $this->computeOrderAccessibility();

        $orderContext = null;
        try {
            $orderContext = $this->cartAccessMode->getOrderContext();
        } catch (NotFoundException $e) {
            //nth
        }

        $args->getSubject()
            ->View()
            ->assign([
                'orderAllowed' => !$this->cartAccessResult->hasErrors(),
                'orderErrorMessages' => $this->cartAccessResult->getErrors(),
                'orderContext' => $orderContext,
                'orderInformationMessages' => $this->cartAccessResult->information,
            ]);
    }

    /**
     * @throws B2bControllerRoutingException
     */
    public function handleOrderBeforeCreate()
    {
        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $identity = $this->authenticationService
            ->getIdentity();

        $this->computeOrderAccessibility();

        try {
            $this->cartAccessMode
                ->handleOrder($identity, $this->cartAccessResult);
        } catch (B2bControllerRoutingException $e) {
            $this->triggerOrderContextEvent();
            throw $e;
        }
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function finishOrder(\Enlight_Hook_HookArgs $args)
    {
        $orderNumber = (string) $args->getReturn();
        $args->setReturn($orderNumber);

        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $this->cartAccessMode
            ->handleCreatedOrder($orderNumber, $ownershipContext);

        $this->triggerOrderContextEvent();
    }

    private function triggerOrderContextEvent()
    {
        $this->eventManager->notify(
            self::EVENT_NAME,
            [
                'orderContext' => $this->cartAccessMode->getOrderContext(),
            ]
        );
    }

    private function computeOrderAccessibility()
    {
        $identity = $this->authenticationService->getIdentity();

        $order = $this->entityFactory
            ->createOrderEntityFromBasketArray(Shopware()->Modules()->Basket()->sGetBasket(), $identity->getOwnershipContext());

        $this->cartAccessResult = $this->cartService
            ->computeAccessibility(
                $identity,
                $order,
                CartService::ENVIRONMENT_NAME_ORDER
            );
    }

    public function checkCartState()
    {
        if ($this->cartState->hasState()) {
            return;
        }

        if (!$this->cartState->hasOldState()) {
            return;
        }

        $this->eventManager->notify(
            self::CHANGE_TO_OLD_STATE,
            [
                'oldState' => $this->cartState->getOldState(),
            ]
        );
    }
}
