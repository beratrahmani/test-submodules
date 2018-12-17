<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Framework;

use Shopware\B2B\Common\CrudEntity;

class ContingentGroupAssignmentEntity extends ContingentGroupEntity
{
    /**
     * @var int
     */
    public $assignmentId;

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
