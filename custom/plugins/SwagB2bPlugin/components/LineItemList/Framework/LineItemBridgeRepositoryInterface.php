<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

interface LineItemBridgeRepositoryInterface
{
    /**
     * @param string $cartId
     * @return array
     */
    public function fetchCartDataById(string $cartId): array;
}
