<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class AuditLogEntity implements CrudEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var AuditLogValueBasicEntity
     */
    public $logValue;

    /**
     * @var string
     */
    public $logType;

    /**
     * @var string
     */
    public $eventDate;

    /**
     * @var string
     */
    public $authorHash;

    /**
     * @var Identity
     */
    public $authorIdentity;

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
            'log_value' => serialize($this->logValue),
            'log_type' => $this->logType,
            'author_hash' => $this->authorHash,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->logType = $data['log_type'];
        $this->logValue = unserialize($data['log_value'], [true]);
        $this->eventDate = $data['event_date'];
        $this->authorHash = $data['author_hash'];

        return $this;
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
