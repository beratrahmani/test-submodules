<?php declare(strict_types=1);

namespace Shopware\B2B\Currency\Framework;

class CurrencyCalculator
{
    /**
     * @param CurrencyAware[] $currencyAwares
     * @param CurrencyContext $currencyContext
     */
    public function recalculateAmounts(array $currencyAwares, CurrencyContext $currencyContext)
    {
        foreach ($currencyAwares as $currencyAware) {
            $this->recalculateAmount($currencyAware, $currencyContext);
        }
    }

    /**
     * @param CurrencyAware $currencyAware
     * @param CurrencyContext $currencyContext
     * @return CurrencyAware
     */
    public function recalculateAmount(CurrencyAware $currencyAware, CurrencyContext $currencyContext): CurrencyAware
    {
        foreach ($currencyAware->getAmountPropertyNames() as $propertyName) {
            $currencyAware->{$propertyName} = ($currencyAware->{$propertyName} / $currencyAware->getCurrencyFactor()) * $currencyContext->currentCurrencyFactor;
        }

        return $currencyAware;
    }

    /**
     * @param string $amountPropertyName
     * @param string $factorPropertyName
     * @param CurrencyContext $currencyContext
     * @param string|null $tableAlias
     * @return string
     */
    public function getSqlCalculationPart(
        string $amountPropertyName,
        string $factorPropertyName,
        CurrencyContext $currencyContext,
        string $tableAlias = null
    ): string {
        if ($tableAlias) {
            return "(($tableAlias.$amountPropertyName/$tableAlias.$factorPropertyName) * {$currencyContext->currentCurrencyFactor})";
        }

        return "(($amountPropertyName/$factorPropertyName) * {$currencyContext->currentCurrencyFactor})";
    }
}
