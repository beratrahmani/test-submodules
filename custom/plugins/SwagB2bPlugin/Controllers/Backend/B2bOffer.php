<?php declare(strict_types=1);

use Shopware\B2B\Common\Backend\ControllerProxy;

class Shopware_Controllers_Backend_B2bOffer extends ControllerProxy
{
    /**
     * @see \Shopware\B2B\Offer\Backend\OfferController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_offer.backend_controller';
    }
}
