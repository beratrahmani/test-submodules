<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bRoleRoleVisibility extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Role\Frontend\RoleRoleVisibilityController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_role.role_role_visibility_controller';
    }
}
