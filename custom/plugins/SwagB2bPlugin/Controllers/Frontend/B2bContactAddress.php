<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContactAddress extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Address\Frontend\ContactAddressController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_address.contact_controller';
    }
}
