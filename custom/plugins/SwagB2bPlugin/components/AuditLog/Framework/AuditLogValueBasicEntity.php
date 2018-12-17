<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

abstract class AuditLogValueBasicEntity
{
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
     * @return string
     */
    abstract public function getTemplateName(): string;

    /**
     * @return bool
     */
    abstract public function isChanged(): bool;
}
