<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Framework;

interface ShopServiceInterface
{
    /**
     * @return int
     */
    public function getRootCategoryId(): int;

    /**
     * @return float
     */
    public function getCurrentCurrencyFactor(): float;

    /**
     * @return string
     */
    public function getCurrentCurrencySymbol(): string;
}
