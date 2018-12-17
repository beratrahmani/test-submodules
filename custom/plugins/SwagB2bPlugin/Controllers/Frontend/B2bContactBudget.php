<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContactBudget extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Budget\Frontend\ContactBudgetController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_budget.contact_controller';
    }
}
