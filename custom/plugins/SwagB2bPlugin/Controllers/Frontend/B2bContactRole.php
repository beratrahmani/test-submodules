<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContactRole extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\RoleContact\Frontend\ContactRoleAssignmentController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_role_contact.controller';
    }
}
