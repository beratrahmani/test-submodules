<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

interface LineItemShopWriterServiceInterface
{
    /**
     * @param LineItemList $list
     * @param bool $clearBasket
     * @return array raw basket array
     */
    public function triggerCart(LineItemList $list, bool $clearBasket): array;
}
