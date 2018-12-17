<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Framework;

use Shopware\B2B\Common\CrudEntity;

class ContingentGroupEntity implements CrudEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * foreign key to s_user.id
     *
     * @var int
     */
    public $contextOwnerId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * {@inheritdoc}
     */
    public function isNew(): bool
    {
        return !(bool) $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => (int) $this->id,
            'context_owner_id' => (int) $this->contextOwnerId,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->contextOwnerId = (int) $data['context_owner_id'];
        $this->name = $data['name'];
        $this->description = $data['description'];

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
}
