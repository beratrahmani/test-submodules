<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Framework;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Role\Framework\AclTableContactContextResolver;
use Shopware\B2B\Role\Framework\AclTableRoleContextResolver;

class ContingentGroupRoleAclTable extends AclTable
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(
            'role_contingent_group',
            'b2b_role',
            'id',
            'b2b_contingent_group',
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
