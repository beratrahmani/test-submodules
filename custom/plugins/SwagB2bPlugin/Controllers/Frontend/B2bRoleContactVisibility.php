<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bRoleContactVisibility extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\RoleContact\Frontend\RoleContactVisibilityController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_role_contact.role_contact_visibility_controller';
    }
}
