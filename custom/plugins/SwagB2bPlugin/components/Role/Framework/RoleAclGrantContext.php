<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Common\Entity;

class RoleAclGrantContext implements AclGrantContext
{
    /**
     * @var RoleEntity
     */
    private $roleEntity;

    /**
     * @param RoleEntity $roleEntity
     */
    public function __construct(RoleEntity $roleEntity)
    {
        $this->roleEntity = $roleEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity(): Entity
    {
        return $this->roleEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return RoleEntity::class . '::' . $this->roleEntity->id;
    }
}
