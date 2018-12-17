<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Shopware\B2B\Cart\Framework\CartStateInterface;
use Shopware\B2B\Offer\Framework\OfferEntity;
use Shopware\B2B\Offer\Framework\OfferShopWriterServiceInterface;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Shop\Framework\SessionStorageInterface;

class OfferShopWriterService implements OfferShopWriterServiceInterface
{
    /**
     * @var SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var CartStateInterface
     */
    private $cartState;

    /**
     * @param SessionStorageInterface $sessionStorage
     * @param CartStateInterface $cartState
     */
    public function __construct(SessionStorageInterface $sessionStorage, CartStateInterface $cartState)
    {
        $this->sessionStorage = $sessionStorage;
        $this->cartState = $cartState;
    }

    /**
     * @param OfferEntity $offerEntity
     * @param OrderContext $orderContext
     */
    public function sendToCheckout(OrderContext $orderContext)
    {
        $this->cartState->setState(CartAccessModeOfferCheckout::NAME);
        $this->cartState->setStateId($orderContext->id);
    }

    public function stopCheckout()
    {
        $this->cartState->resetStateId();
        $this->cartState->resetState();
        $this->cartState->resetOldState();
        Shopware()->Modules()->Basket()->clearBasket();
        $this->sessionStorage->set('sBasketCurrency', Shopware()->Shop()->getCurrency()->getId());
        $this->sessionStorage->set('sBasketQuantity', 0);
        $this->sessionStorage->set('sBasketAmount', 0);
    }
}
