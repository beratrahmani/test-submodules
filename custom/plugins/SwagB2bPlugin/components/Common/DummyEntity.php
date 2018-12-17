<?php declare(strict_types=1);

namespace Shopware\B2B\Common;

class DummyEntity implements CrudEntity
{
    public function isNew(): bool
    {
        return true;
    }

    public function toDatabaseArray(): array
    {
        return [];
    }

    public function fromDatabaseArray(array $data): CrudEntity
    {
        return $this;
    }

    public function jsonSerialize()
    {
        return [];
    }
}
