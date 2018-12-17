<?php declare(strict_types=1);

use Shopware\B2B\Common\Backend\ControllerProxy;

class Shopware_Controllers_Backend_B2bOfferLineItemReference extends ControllerProxy
{
    /**
     * @see \Shopware\B2B\Offer\Backend\OfferLineItemReferenceController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_offer.backend_line_item_reference_controller';
    }
}
