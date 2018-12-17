<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bOfferLineItemReference extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Offer\Frontend\OfferLineItemReferenceController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_offer.line_item_reference_controller';
    }
}
