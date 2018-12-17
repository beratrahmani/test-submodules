<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Shopware\B2B\Debtor\Framework\DebtorIdentity;

class SalesRepresentativeIdentity extends DebtorIdentity implements SalesRepresentativeIdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public function isSuperAdmin(): bool
    {
        return false;
    }
}
