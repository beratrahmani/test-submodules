<?php declare(strict_types=1);

namespace Shopware\B2B\Currency\Framework;

interface CurrencyAware
{
    const DEFAULT_FACTOR = 1.0;

    /**
     * @return float
     */
    public function getCurrencyFactor(): float;

    /**
     * @param float $factor
     */
    public function setCurrencyFactor(float $factor);

    /**
     * @return string[]
     */
    public function getAmountPropertyNames(): array;
}
