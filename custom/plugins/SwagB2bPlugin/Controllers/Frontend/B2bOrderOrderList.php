<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bOrderOrderList extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\OrderOrderList\Frontend\OrderOrderListController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_order_order_list.controller';
    }
}
