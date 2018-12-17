<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\ProductPriceType;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleEntity;
use Shopware\B2B\Currency\Framework\CurrencyAware;

class ProductPriceRuleEntity extends ContingentRuleEntity implements CurrencyAware
{
    /**
     * @var float
     */
    public $productPrice;

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
                'product_price' => $this->productPrice,
                'currency_factor' => $this->currencyFactor,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->productPrice = (float) $data['product_price'];
        $this->currencyFactor = (float) $data['currency_factor'];

        return parent::fromDatabaseArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArrayPrefixed(array $data): CrudEntity
    {
        $this->productPrice = (float) $data[$data['type'] . '_product_price'];
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
    public function getAmountPropertyNames(): array
    {
        return [
            'productPrice',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyFactor(float $factor)
    {
        $this->currencyFactor = $factor;
    }
}
