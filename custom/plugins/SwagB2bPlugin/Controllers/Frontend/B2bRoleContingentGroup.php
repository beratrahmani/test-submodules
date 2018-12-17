<?php declare(strict_types=1);

use Shopware\B2B\Common\Frontend\ControllerProxy;

class Shopware_Controllers_Frontend_B2bRoleContingentGroup extends ControllerProxy
{
    /**
     * @see \Shopware\B2B\RoleContingentGroup\Frontend\RoleContingentGroupAssignmentController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_role_contingent_group.controller';
    }
}
