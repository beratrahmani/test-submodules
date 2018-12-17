<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bOrderClearance extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\OrderClearance\Frontend\OrderClearanceController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_order_clearance.controller';
    }
}
