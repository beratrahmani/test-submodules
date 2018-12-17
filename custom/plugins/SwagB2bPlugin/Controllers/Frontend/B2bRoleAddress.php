<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bRoleAddress extends LoginProtectedControllerProxy
{
    /**
      * @see \Shopware\B2B\RoleAddress\Frontend\RoleAddressAssignmentController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_role_address.controller';
    }
}
