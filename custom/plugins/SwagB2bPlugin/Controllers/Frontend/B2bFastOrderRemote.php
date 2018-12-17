<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bFastOrderRemote extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\FastOrder\Frontend\FastOrderRemoteController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_fast_order.remote_controller';
    }
}
