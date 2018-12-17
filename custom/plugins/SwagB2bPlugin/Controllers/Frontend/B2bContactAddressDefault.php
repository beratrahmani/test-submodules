<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContactAddressDefault extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Address\Frontend\ContactAddressDefaultController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_address.contact_default_controller';
    }
}
