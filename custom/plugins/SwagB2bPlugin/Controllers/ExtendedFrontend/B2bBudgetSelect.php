<?php declare(strict_types=1);

use Shopware\B2B\Common\Frontend\ControllerProxy;

class Shopware_Controllers_Frontend_B2bBudgetSelect extends ControllerProxy
{
    /**
     * @see \Shopware\B2B\Budget\Frontend\BudgetSelectController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_budget.select_controller';
    }
}
