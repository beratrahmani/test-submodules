<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogValueDiffEntity;
use Shopware\B2B\Currency\Framework\CurrencyAware;
use Shopware\B2B\ProductName\Framework\ProductNameAware;

class AuditLogValueLineItemPriceEntity extends AuditLogValueDiffEntity implements CurrencyAware, ProductNameAware
{
    /**
     * @var float
     */
    public $currencyFactor;

    /**
     * @var string
     */
    public $orderNumber;

    /**
     * @var string
     */
    public $productName;

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'OfferLineItemPriceChange';
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
            'newValue',
            'oldValue',
        ];
    }

    /**
     * @param string $name
     */
    public function setProductName(string $name = null)
    {
        $this->productName = $name;
    }

    /**
     * @return string
     */
    public function getProductOrderNumber(): string
    {
        return $this->orderNumber;
    }
}
