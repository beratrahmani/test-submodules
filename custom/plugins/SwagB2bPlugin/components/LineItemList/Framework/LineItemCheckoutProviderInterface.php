<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

interface LineItemCheckoutProviderInterface
{
    /**
     * @param LineItemListSource $source
     * @return LineItemList
     */
    public function createList(LineItemListSource $source): LineItemList;

    /**
     * @param string $cartId
     * @return LineItemList
     */
    public function createListFromCartId(string $cartId): LineItemList;

    /**
     * @param LineItemList $list
     * @param LineItemListSource $lineItemListSources
     * @return LineItemList
     */
    public function updateList(LineItemList $list, LineItemListSource $lineItemListSources): LineItemList;
}
