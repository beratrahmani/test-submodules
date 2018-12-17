<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\Currency\Framework\CurrencyAware;

class LineItemList implements CrudEntity, CurrencyAware
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $contextOwnerId;

    /**
     * @var LineItemReference[]
     */
    public $references = [];

    /**
     * @var float
     */
    public $amount;

    /**
     * @var float
     */
    public $amountNet;

    /**
     * @var float
     */
    public $currencyFactor = self::DEFAULT_FACTOR;

    /**
     * {@inheritdoc}
     */
    public function isNew(): bool
    {
        return ! (bool) $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'context_owner_id' => $this->contextOwnerId,
            'amount' => $this->amount,
            'amount_net' => $this->amountNet,
            'currency_factor' => $this->currencyFactor,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->contextOwnerId = (int) $data['context_owner_id'];
        $this->amount = $data['amount'];
        $this->amountNet = $data['amount_net'];
        $this->setCurrencyFactor((float) $data['currency_factor']);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param int $id
     * @throws \InvalidArgumentException
     * @return LineItemReference
     */
    public function getReferenceById(int $id): LineItemReference
    {
        foreach ($this->references as $reference) {
            if ($reference->id === $id) {
                return $reference;
            }
        }

        throw new \InvalidArgumentException('The line item reference with id "' . $id . '" is not a part of this entity');
    }

    /**
     * @param string $number
     * @throws \InvalidArgumentException
     * @return LineItemReference
     */
    public function getReferenceByNumber(string $number): LineItemReference
    {
        foreach ($this->references as $reference) {
            if (strcasecmp($reference->referenceNumber, $number) === 0) {
                return $reference;
            }
        }

        throw new \InvalidArgumentException('The line item reference with reference number "' . $number . '" is not a part of this entity');
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
            'amount',
            'amountNet',
        ];
    }
}
