<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContactRoleVisibility extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\RoleContact\Frontend\ContactRoleVisibilityController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_role_contact.contact_role_visibility_controller';
    }
}
