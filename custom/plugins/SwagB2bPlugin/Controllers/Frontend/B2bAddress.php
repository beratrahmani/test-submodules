<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bAddress extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Address\Frontend\AddressController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_address.controller';
    }
}
