<?php declare(strict_types=1);

use Shopware\B2B\Common\Frontend\ControllerProxy;

class Shopware_Controllers_Frontend_B2bContingentRuleCategory extends ControllerProxy
{
    /**
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_contingent_rule.category_controller';
    }
}
