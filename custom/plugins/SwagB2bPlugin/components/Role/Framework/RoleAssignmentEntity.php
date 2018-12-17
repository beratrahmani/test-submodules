<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\Common\CrudEntity;

class RoleAssignmentEntity extends RoleEntity
{
    /**
     * @var int
     */
    public $assignmentId;

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        $array = parent::toDatabaseArray();

        $array['assignmentId'] = $this->assignmentId;

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $roleData): CrudEntity
    {
        parent::fromDatabaseArray($roleData);

        $this->assignmentId = $roleData['assignmentId'];

        return $this;
    }
}
