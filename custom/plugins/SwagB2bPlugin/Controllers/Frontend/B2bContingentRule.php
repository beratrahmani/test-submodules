<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContingentRule extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\ContingentRule\Frontend\ContingentRuleController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_contingent_rule.contingent_rule_controller';
    }
}
