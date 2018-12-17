<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bOrderListRemote extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\OrderList\Frontend\OrderListRemoteController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_order_list.remote_controller';
    }
}
