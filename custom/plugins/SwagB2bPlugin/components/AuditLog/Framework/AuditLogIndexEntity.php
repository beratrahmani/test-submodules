<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

use Shopware\B2B\Common\CrudEntity;

class AuditLogIndexEntity implements CrudEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $auditLogId;

    /**
     * @var string
     */
    public $referenceTable;

    /**
     * @var int
     */
    public $referenceId;

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
            'audit_log_id' => (int) $this->auditLogId,
            'reference_table' => $this->referenceTable,
            'reference_id' => (int) $this->referenceId,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->auditLogId = (int) $data['audit_log_id'];
        $this->referenceTable = $data['reference_table'];
        $this->referenceId = (int) $data['reference_id'];

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
