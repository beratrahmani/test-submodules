<?php declare(strict_types=1);

use Shopware\B2B\Common\Frontend\ControllerProxy;

class Shopware_Controllers_Frontend_B2bContingentRestriction extends ControllerProxy
{
    /**
     * @see \Shopware\B2B\ContingentRule\Frontend\ContingentRuleController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_contingent_rule.contingent_restriction_controller';
    }
}
