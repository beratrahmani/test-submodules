<?php declare(strict_types=1);

namespace Shopware\B2B\Currency\Framework;

class CurrencyContext
{
    /**
     * @var float
     */
    public $currentCurrencyFactor;

    /**
     * @param float $currentCurrencyFactor
     */
    public function __construct(float $currentCurrencyFactor)
    {
        $this->currentCurrencyFactor = $currentCurrencyFactor;
    }
}
