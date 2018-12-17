<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Framework;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Role\Framework\AclTableContactContextResolver;
use Shopware\B2B\Role\Framework\AclTableRoleContextResolver;

class OrderListRoleAclTable extends AclTable
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(
            'role_order_list',
            'b2b_role',
            'id',
            'b2b_order_list',
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
