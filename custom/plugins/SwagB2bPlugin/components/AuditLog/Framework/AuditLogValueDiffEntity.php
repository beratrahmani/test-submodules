<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

class AuditLogValueDiffEntity extends AuditLogValueBasicEntity
{
    /**
     * @var string
     */
    public $oldValue;

    /**
     * @var string
     */
    public $newValue;

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return 'OrderClearanceStatusChange';
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->newValue !== $this->oldValue;
    }
}
