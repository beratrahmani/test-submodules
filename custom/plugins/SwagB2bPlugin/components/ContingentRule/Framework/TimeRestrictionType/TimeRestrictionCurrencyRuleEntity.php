<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\TimeRestrictionType;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\Currency\Framework\CurrencyAware;

class TimeRestrictionCurrencyRuleEntity extends TimeRestrictionRuleEntity implements CurrencyAware
{
    /**
     * @var float
     */
    public $currencyFactor = self::DEFAULT_FACTOR;

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return array_merge(
            parent::toDatabaseArray(),
            [
                'currency_factor' => $this->currencyFactor,
        ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->currencyFactor = (float) $data['currency_factor'];

        return parent::fromDatabaseArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArrayPrefixed(array $data): CrudEntity
    {
        $this->currencyFactor = (float) $data[$data['type'] . '_currency_factor'];

        return parent::fromDatabaseArrayPrefixed($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyFactor(): float
    {
        return $this->currencyFactor;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyFactor(float $factor)
    {
        $this->currencyFactor = $factor;
    }

    /**
     * {@inheritdoc}
     */
    public function getAmountPropertyNames(): array
    {
        return [
            'value',
        ];
    }
}
