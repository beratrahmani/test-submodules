<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\Common\CrudEntity;

class RoleEntity implements CrudEntity
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
    public $contextOwnerId;

    /**
     * @var int
     */
    public $left;

    /**
     * @var int
     */
    public $right;

    /**
     * @var int
     */
    public $level;

    /**
     * @var bool
     */
    public $hasChildren;

    /**
     * @var array
     */
    public $children = [];

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return !(bool) $this->id;
    }

    /**
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            '`context_owner_id`' => $this->contextOwnerId,
        ];
    }

    /**
     * @param array $roleData
     * @return CrudEntity
     */
    public function fromDatabaseArray(array $roleData): CrudEntity
    {
        $this->id = (int) $roleData['id'];
        $this->name = (string) $roleData['name'];
        $this->contextOwnerId = (int) $roleData['context_owner_id'];
        $this->left = (int) $roleData['left'];
        $this->right = (int) $roleData['right'];
        $this->level = (int) $roleData['level'];
        $this->hasChildren = (bool) $roleData['hasChildren'];

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
