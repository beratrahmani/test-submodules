<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bDashboard extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Dashboard\Frontend\DashboardController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_dashboard.controller';
    }
}
