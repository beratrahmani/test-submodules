<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

interface CartAccessFactoryInterface
{
    /**
     * @param Identity $identity
     * @param string $environmentName
     * @return CartAccessStrategyInterface
     */
    public function createCartAccessForIdentity(Identity $identity, string $environmentName): CartAccessStrategyInterface;
}
