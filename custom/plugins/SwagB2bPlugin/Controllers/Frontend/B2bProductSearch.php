<?php declare(strict_types=1);

use SwagB2bPlugin\Extension\LoginProtectedControllerProxy;

class Shopware_Controllers_Frontend_B2bProductSearch extends LoginProtectedControllerProxy
{
    /**
     * @see \Shopware\B2B\ProductSearch\Frontend\ProductSearchController
     * @return string
     */
    protected function getControllerDiKey(): string
    {
        return 'b2b_product_search.controller';
    }
}
