<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bFastOrder extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\FastOrder\Frontend\FastOrderController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_fast_order.controller';
    }
}
