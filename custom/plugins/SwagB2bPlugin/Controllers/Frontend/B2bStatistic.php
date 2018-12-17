<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bStatistic extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\Statistic\Frontend\StatisticController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_statistic.controller';
    }
}
