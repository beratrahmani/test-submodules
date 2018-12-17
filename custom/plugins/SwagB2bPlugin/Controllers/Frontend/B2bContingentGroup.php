<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContingentGroup extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\ContingentGroup\Frontend\ContingentGroupController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_contingent_group.controller';
    }
}
