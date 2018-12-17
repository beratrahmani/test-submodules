<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\ProductName\Framework\ProductNameAware;

class LineItemReference implements CrudEntity, ProductNameAware
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $referenceNumber;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var string
     */
    public $comment;

    /**
     * @var string
     */
    public $amount;

    /**
     * @var string
     */
    public $amountNet;

    /**
     * @var int
     */
    public $mode;

    /**
     * @var int
     */
    public $sort;

    /**
     * @var float
     */
    public $maxPurchase;

    /**
     * @var float
     */
    public $minPurchase;

    /**
     * @var float
     */
    public $purchaseStep;

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return !(bool) $this->id;
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->mode === 0;
    }

    /**
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->referenceNumber,
            'quantity' => $this->quantity,
            'comment' => (string) $this->comment,
            'amount' => $this->amount,
            'amount_net' => $this->amountNet,
            'mode' => $this->mode,
            'sort' => $this->sort,
        ];
    }

    /**
     * @param array $data
     * @return CrudEntity
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->referenceNumber = (string) $data['reference_number'];
        $this->quantity = (int) $data['quantity'];
        $this->comment = (string) $data['comment'];
        $this->amount = $data['amount'];
        $this->amountNet = $data['amount_net'];
        $this->mode = (int) $data['mode'];
        $this->sort = (int) $data['sort'];

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

        if ($this->id) {
            $this->id = (int) $this->id;
        }

        if ($this->quantity !== null) {
            $this->quantity = (int) $this->quantity;
        }

        if ($this->amount) {
            $this->amount = (float) $this->amount;
        }

        if ($this->amountNet) {
            $this->amountNet = (float) $this->amountNet;
        }

        if ($this->mode !== null) {
            $this->mode = (int) $this->mode;
        }

        if ($this->sort !== null) {
            $this->sort = (int) $this->sort;
        }
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
     * {@inheritdoc}
     */
    public function setProductName(string $name = null)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductOrderNumber(): string
    {
        return $this->referenceNumber;
    }
}
