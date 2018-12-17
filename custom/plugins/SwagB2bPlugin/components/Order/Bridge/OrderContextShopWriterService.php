<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextShopWriterServiceInterface;

class OrderContextShopWriterService implements OrderContextShopWriterServiceInterface
{
    /**
     * @param OrderContext $orderContext
     */
    public function extendCart(OrderContext $orderContext)
    {
        //preselect addresses
        Shopware()->Session()->offsetSet('checkoutShippingAddressId', $orderContext->shippingAddressId);
        Shopware()->Session()->offsetSet('checkoutBillingAddressId', $orderContext->billingAddressId);

        //preselect Dispatch
        Shopware()->Session()->offsetSet('sDispatch', $orderContext->shippingId);
    }
}
