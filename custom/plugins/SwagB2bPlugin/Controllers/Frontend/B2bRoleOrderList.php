<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bRoleOrderList extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\OrderList\Frontend\RoleOrderListController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_order_list.role_controller';
    }
}
