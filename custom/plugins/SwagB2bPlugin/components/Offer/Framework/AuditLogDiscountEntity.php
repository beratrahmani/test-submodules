<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogValueDiffEntity;
use Shopware\B2B\Currency\Framework\CurrencyAware;

class AuditLogDiscountEntity extends AuditLogValueDiffEntity implements CurrencyAware
{
    /**
     * @var float
     */
    public $currencyFactor;

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
            'newValue',
            'oldValue',
        ];
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'OfferDiscount';
    }
}
