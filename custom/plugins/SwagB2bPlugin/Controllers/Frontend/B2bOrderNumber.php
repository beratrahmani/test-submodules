<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bOrderNumber extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\OrderNumber\Frontend\OrderNumberController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_order_number.controller';
    }
}
