<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\LineItemList\Framework\LineItemReference;

class OfferLineItemReferenceEntity extends LineItemReference
{
    const DEFAULT_FACTOR = 1.0;

    /**
     * @var float
     */
    public $discountAmount;

    /**
     * @var float
     */
    public $discountAmountNet;

    /**
     * @var float
     */
    public $discountCurrencyFactor = self::DEFAULT_FACTOR;

    /**
     * @return array
     */
    public function toDatabaseArray(): array
    {
        $data = [
            'discount_amount' => $this->discountAmount,
            'discount_amount_net' => $this->discountAmountNet,
            'discount_currency_factor' => $this->discountCurrencyFactor,
        ];

        return array_merge($data, parent::toDatabaseArray());
    }

    /**
     * @param array $data
     * @return CrudEntity
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        parent::fromDatabaseArray($data);

        $this->discountAmount = (float) $this->amount;

        if ($data['discount_amount'] !== null) {
            $this->discountAmount = (float) $data['discount_amount'];
        }

        $this->discountAmountNet = (float) $this->amountNet;

        if ($data['discount_amount_net'] !== null) {
            $this->discountAmountNet = (float) $data['discount_amount_net'];
        }

        if ($data['discount_currency_factor'] !== null) {
            $this->discountCurrencyFactor = (float) $data['discount_currency_factor'];
        }

        return $this;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }

        parent::setData($data);

        if ($this->discountAmount === null) {
            $this->discountAmount = (float) $this->amount;
        }

        if ($this->discountAmountNet === null) {
            $this->discountAmountNet = (float) $this->amountNet;
        }
    }
}
