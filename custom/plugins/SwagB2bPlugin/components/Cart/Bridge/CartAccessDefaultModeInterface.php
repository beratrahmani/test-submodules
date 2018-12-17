<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Common\Controller\B2bControllerRoutingException;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface CartAccessDefaultModeInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Enable the context by modifying the cart here
     *
     * @param OwnershipContext $ownershipContext
     */
    public function enable(OwnershipContext $ownershipContext);

    /**
     * just before the order is created reroute here by throwing a mvc exception
     *
     * @param Identity $identity
     * @param CartAccessResult $cartAccessResult
     * @throws B2bControllerRoutingException
     */
    public function handleOrder(Identity $identity, CartAccessResult $cartAccessResult);

    /**
     * Handle the ordernumber created here
     *
     * @param string $orderNumber
     * @param OwnershipContext $ownershipContext
     */
    public function handleCreatedOrder(string $orderNumber, OwnershipContext $ownershipContext);

    /**
     * @return OrderContext
     */
    public function getOrderContext(): OrderContext;
}
