<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Bridge;

use Shopware\B2B\Cart\Framework\CartStateInterface;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceEntity;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceShopWriterServiceInterface;

class OrderClearanceShopWriterService implements OrderClearanceShopWriterServiceInterface
{
    /**
     * @var CartStateInterface
     */
    private $cartState;

    /**
     * @param CartStateInterface $cartState
     */
    public function __construct(CartStateInterface $cartState)
    {
        $this->cartState = $cartState;
    }

    /**
     * @param OrderClearanceEntity $orderClearance
     */
    public function sendToClearance(OrderClearanceEntity $orderClearance)
    {
        $this->cartState->setStateId($orderClearance->id);
        $this->cartState->setState('clearance');
    }

    public function stopOrderClearance()
    {
        $this->cartState->resetStateId();
        $this->cartState->resetState();
        $this->cartState->resetOldState();

        Shopware()->Modules()->Basket()->clearBasket();
        Shopware()->Modules()->Basket()->sRefreshBasket();
    }
}
