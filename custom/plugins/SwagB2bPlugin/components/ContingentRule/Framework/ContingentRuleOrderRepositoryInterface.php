<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface ContingentRuleOrderRepositoryInterface
{
    /**
     * @param OwnershipContext $ownershipContext
     * @param string $timeRestriction
     * @return ContingentRuleOrder
     */
    public function fetchOrderHistory(OwnershipContext $ownershipContext, string $timeRestriction): ContingentRuleOrder;
}
