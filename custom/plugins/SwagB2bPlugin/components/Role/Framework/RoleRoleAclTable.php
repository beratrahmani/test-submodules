<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\Acl\Framework\AclTable;

class RoleRoleAclTable extends AclTable
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(
            'role_role',
            'b2b_role',
            'id',
            'b2b_role',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getContextResolvers(): array
    {
        return [
            new AclTableRoleContextResolver(),
            new AclTableContactContextResolver(),
        ];
    }
}
