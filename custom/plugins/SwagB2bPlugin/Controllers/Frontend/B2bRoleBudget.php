<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bRoleBudget extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\RoleBudget\Frontend\RoleBudgetAssignmentController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_role_budget.controller';
    }
}
