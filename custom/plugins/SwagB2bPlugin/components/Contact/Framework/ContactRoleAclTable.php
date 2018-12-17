<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Role\Framework\AclTableContactContextResolver;
use Shopware\B2B\Role\Framework\AclTableRoleContextResolver;

class ContactRoleAclTable extends AclTable
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(
            'role_contact',
            'b2b_role',
            'id',
            'b2b_debtor_contact',
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
