<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContactRoute extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Contact\Frontend\ContactRouteController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_contact.route_controller';
    }
}
