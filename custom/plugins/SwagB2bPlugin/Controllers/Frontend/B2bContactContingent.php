<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bContactContingent extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\ContingentGroupContact\Frontend\ContactContingentController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_contingent_group_contact.controller';
    }
}
