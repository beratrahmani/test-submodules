<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Bridge;

use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemShopWriterServiceInterface;

class LineItemShopWriterService implements LineItemShopWriterServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function triggerCart(LineItemList $list, bool $clearBasket): array
    {
        if ($clearBasket) {
            Shopware()->Modules()->Basket()->clearBasket();
        }

        foreach ($list->references as $product) {
            @Shopware()->Modules()->Basket()->sAddArticle($product->referenceNumber, $product->quantity);
        }

        @Shopware()->Modules()->Basket()->sRefreshBasket();

        return @Shopware()->Modules()->Basket()->sGetBasket();
    }
}
