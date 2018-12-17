<?php declare(strict_types=1);

namespace Shopware\B2B\Common;

interface CrudEntity extends Entity, \JsonSerializable
{
    /**
     * @return bool
     */
    public function isNew(): bool;

    /**
     * @return array
     */
    public function toDatabaseArray(): array;

    /**
     * @param array $data
     * @return CrudEntity
     */
    public function fromDatabaseArray(array $data): self;
}
