<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Role\Framework\AclTableContactContextResolver;
use Shopware\B2B\Role\Framework\AclTableRoleContextResolver;

class AddressRoleAclTable extends AclTable
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(
            'role_address',
            'b2b_role',
            'id',
            's_user_addresses',
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
