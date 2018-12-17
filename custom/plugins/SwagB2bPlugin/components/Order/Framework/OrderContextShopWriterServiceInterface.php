<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

interface OrderContextShopWriterServiceInterface
{
    /**
     * @param OrderContext $orderContext
     */
    public function extendCart(OrderContext $orderContext);
}
