<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class CartStateSubscriber implements SubscriberInterface
{
    /**
     * @var CartState
     */
    private $cartState;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuthenticationService $authenticationService
     * @param CartState $cartState
     */
    public function __construct(
        AuthenticationService $authenticationService,
        CartState $cartState
    ) {
        $this->cartState = $cartState;
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Controllers_Frontend_Account::logoutAction::before' => 'resetCardState',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function resetCardState(\Enlight_Hook_HookArgs $args)
    {
        if (!$this->authenticationService->isB2b()) {
            return;
        }

        $this->cartState->resetStateId();
        $this->cartState->resetState();
        $this->cartState->resetOldState();
        Shopware()->Modules()->Basket()->clearBasket();
    }
}
