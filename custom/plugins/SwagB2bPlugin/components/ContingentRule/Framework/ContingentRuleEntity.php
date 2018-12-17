<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Shopware\B2B\Common\CrudEntity;

abstract class ContingentRuleEntity implements CrudEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * foreign key to b2b_contingent_group.id
     *
     * @var int
     */
    public $contingentGroupId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $templateTypeName;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
        $this->templateTypeName = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $type));
    }

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
            'contingent_group_id' => (int) $this->contingentGroupId,
            'type' => $this->type,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->contingentGroupId = (int) $data['contingent_group_id'];
        $this->type = $data['type'];

        return $this;
    }

    /**
     * @param array $data
     * @return CrudEntity
     */
    public function fromDatabaseArrayPrefixed(array $data): CrudEntity
    {
        return self::fromDatabaseArray($data);
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $properties = array_keys($this->toArray());

        foreach ($data as $key => $value) {
            if (false === in_array($key, $properties, true)) {
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
