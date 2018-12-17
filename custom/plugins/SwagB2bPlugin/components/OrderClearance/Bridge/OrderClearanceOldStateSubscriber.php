<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Cart\Bridge\CartAccessSubscriber;
use Shopware\B2B\Cart\Bridge\CartState;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OrderClearanceOldStateSubscriber implements SubscriberInterface
{
    /**
     * @var OrderClearanceRepository
     */
    private $orderClearanceRepository;

    /**
     * @var CartState
     */
    private $cartState;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param OrderClearanceRepository $orderClearanceRepository
     * @param CartState $cartState
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        OrderClearanceRepository $orderClearanceRepository,
        CartState $cartState,
        AuthenticationService $authenticationService
    ) {
        $this->orderClearanceRepository = $orderClearanceRepository;
        $this->cartState = $cartState;
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CartAccessSubscriber::CHANGE_TO_OLD_STATE => 'sendToOrderClearance',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function sendToOrderClearance(\Enlight_Event_EventArgs $args)
    {
        $oldState = $args->offsetGet('oldState');

        if ($oldState !== 'orderClearanceEnabled') {
            return;
        }

        $orderContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $this->orderClearanceRepository->sendToOrderClearance($this->cartState->getStateId(), $orderContext);

        $this->cartState->setState('orderClearanceEnabled');
    }
}
