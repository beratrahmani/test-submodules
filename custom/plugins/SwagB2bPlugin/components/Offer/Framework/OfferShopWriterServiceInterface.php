<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Order\Framework\OrderContext;

interface OfferShopWriterServiceInterface
{
    /**
     * @param OrderContext $orderContext
     */
    public function sendToCheckout(OrderContext $orderContext);

    public function stopCheckout();
}
