<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Framework;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Contact\Framework\AclTableContactContextResolver;

class ContingentGroupContactAclTable extends AclTable
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(
            'contact_contingent_group',
            'b2b_debtor_contact',
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
            new AclTableContactContextResolver(),
        ];
    }
}
