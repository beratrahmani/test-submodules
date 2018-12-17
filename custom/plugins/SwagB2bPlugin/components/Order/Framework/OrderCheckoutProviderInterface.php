<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

interface OrderCheckoutProviderInterface
{
    /**
     * @param OrderSource $source
     * @return OrderContext
     */
    public function createOrder(OrderSource $source): OrderContext;

    /**
     * @param OrderSource $source
     * @param OrderContext $order
     * @return OrderContext
     */
    public function updateOrder(OrderSource $source, OrderContext $order): OrderContext;
}
