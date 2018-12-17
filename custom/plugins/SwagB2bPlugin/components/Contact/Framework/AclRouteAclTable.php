<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Shopware\B2B\Acl\Framework\AclTable;

class AclRouteAclTable extends AclTable
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(
            'contact_route_privilege',
            'b2b_debtor_contact',
            'id',
            'b2b_acl_route_privilege',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContextResolvers(): array
    {
        return [
            new AclTableContactContextResolver(),
        ];
    }
}
