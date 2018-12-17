<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Framework;

use Shopware\B2B\Common\CrudEntity;

class OrderNumberEntity implements CrudEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $customOrderNumber;

    /**
     * Read only
     *
     * @var string
     */
    public $orderNumber;

    /**
     * @var int
     */
    public $productDetailsId;

    /**
     * Read only
     *
     * @var string
     */
    public $name;

    /**
     * Read only
     *
     * @var int
     */
    public $contextOwnerId;

    /**
     * @return bool
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
            'id' => (int) $this->id,
            'custom_ordernumber' => $this->customOrderNumber,
            'product_details_id' => (int) $this->productDetailsId,
            'context_owner_id' => (int) $this->contextOwnerId,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->customOrderNumber = $data['custom_ordernumber'];
        $this->orderNumber = $data['ordernumber'];
        $this->productDetailsId = (int) $data['product_details_id'];
        $this->contextOwnerId = $data['context_owner_id'];

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $orderNumberArray = get_object_vars($this);

        return $orderNumberArray;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param array $data
     * @return OrderNumberEntity
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }

        return $this;
    }
}
