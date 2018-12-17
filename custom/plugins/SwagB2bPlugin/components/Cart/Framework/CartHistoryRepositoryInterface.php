<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface CartHistoryRepositoryInterface
{
    /**
     * @param array $timeRestrictions
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @return array
     */
    public function fetchHistory(
        array $timeRestrictions,
        OwnershipContext $ownershipContext,
        CurrencyContext $currencyContext
    ): array;
}
