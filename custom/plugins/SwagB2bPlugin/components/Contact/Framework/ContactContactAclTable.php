<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Shopware\B2B\Acl\Framework\AclTable;

class ContactContactAclTable extends AclTable
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(
            'contact_contact',
            'b2b_debtor_contact',
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
            new AclTableContactContextResolver(),
        ];
    }
}
