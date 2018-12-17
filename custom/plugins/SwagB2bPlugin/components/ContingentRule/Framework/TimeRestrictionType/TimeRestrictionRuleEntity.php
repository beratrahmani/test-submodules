<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\TimeRestrictionType;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleEntity;

class TimeRestrictionRuleEntity extends ContingentRuleEntity
{
    /**
     * @var string
     */
    public $timeRestriction;

    /**
     * @var float
     */
    public $value;

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return array_merge(
            parent::toDatabaseArray(),
            [
                'time_restriction' => $this->timeRestriction,
                'value' => $this->value,
        ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->timeRestriction = $data['time_restriction'];
        $this->value = (float) $data['value'];

        return parent::fromDatabaseArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArrayPrefixed(array $data): CrudEntity
    {
        $this->timeRestriction = $data[$data['type'] . '_time_restriction'];
        $this->value = (float) $data[$data['type'] . '_value'];

        return parent::fromDatabaseArrayPrefixed($data);
    }
}
