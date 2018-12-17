<?php declare(strict_types=1);


use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContact extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Contact\Frontend\ContactController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_contact.controller';
    }
}
