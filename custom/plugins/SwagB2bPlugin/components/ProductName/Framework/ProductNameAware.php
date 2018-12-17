<?php declare(strict_types=1);

namespace Shopware\B2B\ProductName\Framework;

interface ProductNameAware
{
    /**
     * @param string $name
     */
    public function setProductName(string $name = null);

    /**
     * @return string
     */
    public function getProductOrderNumber(): string;
}
