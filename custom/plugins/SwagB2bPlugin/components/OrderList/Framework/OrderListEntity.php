<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Framework;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\LineItemList\Framework\LineItemList;

class OrderListEntity implements CrudEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $budgetId;

    /**
     * @var int
     */
    public $listId;

    /**
     * @var LineItemList
     */
    public $lineItemList;

    /**
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
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'budget_id' => $this->budgetId,
            'list_id' => $this->listId,
            'context_owner_id' => $this->contextOwnerId,
        ];
    }

    /**
     * @param array $orderListData
     * @return CrudEntity
     */
    public function fromDatabaseArray(array $orderListData): CrudEntity
    {
        $this->id = $orderListData['id'];
        $this->name = $orderListData['name'];
        $this->budgetId = $orderListData['budget_id'];
        $this->listId = $orderListData['list_id'];
        $this->contextOwnerId = $orderListData['context_owner_id'];

        $this->castTypes();

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

        $this->castTypes();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $object = get_object_vars($this);

        unset($object['lineItemList']);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @internal
     */
    protected function castTypes()
    {
        if ($this->id) {
            $this->id = (int) $this->id;
        }

        if ($this->listId) {
            $this->listId = (int) $this->listId;
        }

        if ($this->contextOwnerId) {
            $this->contextOwnerId = (int) $this->contextOwnerId;
        }

        if ($this->budgetId) {
            $this->budgetId = (int) $this->budgetId;
        } else {
            $this->budgetId = null;
        }
    }
}
