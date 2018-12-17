<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bOfferThroughCheckout extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Offer\Frontend\OfferThroughCheckoutController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_offer.offer_through_checkout_controller';
    }
}
