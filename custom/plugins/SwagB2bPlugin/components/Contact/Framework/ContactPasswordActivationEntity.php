<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Shopware\B2B\Common\CrudEntity;

class ContactPasswordActivationEntity implements CrudEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var \DateTime
     */
    public $date;

    /**
     * @var string
     */
    public $hash;

    /**
     * @var string|ContactEntity
     */
    public $data;

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
    public function isValid(): bool
    {
        return $this->date->diff(new \DateTime())->days <= 14;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'datum' => $this->date->format('Y-m-d H:i:s'),
            'hash' => $this->hash,
            'data' => serialize($this->data),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->type = $data['type'];
        $this->date = \DateTime::createFromFormat('Y-m-d H:i:s', $data['datum']);
        $this->hash = $data['hash'];
        $this->data = unserialize($data['data'], ['allowed_classes' => [ContactEntity::class]]);

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
