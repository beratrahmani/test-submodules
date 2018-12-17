<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Shopware\B2B\Order\Framework\OrderCheckoutProviderInterface;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderSource;

class OrderCheckoutProvider implements OrderCheckoutProviderInterface
{
    /**
     * @param OrderSource $source
     * @return OrderContext
     */
    public function createOrder(OrderSource $source): OrderContext
    {
        $source = $this->testSource($source);

        $order = new OrderContext();
        $order->billingAddressId = $source->billingAddressId;
        $order->shippingAddressId = $source->shippingAddressId;
        $order->shippingId = $source->dispatchId;
        $order->shippingAmount = $source->shippingAmount;
        $order->shippingAmountNet = $source->shippingAmountNet;
        $order->comment = $source->sComment;
        $order->deviceType = $source->deviceType;
        $order->statusId = 0;

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrder(OrderSource $source, OrderContext $order): OrderContext
    {
        $source = $this->testSource($source);

        $order->billingAddressId = $source->billingAddressId;
        $order->shippingAddressId = $source->shippingAddressId;
        $order->shippingId = $source->dispatchId;
        $order->shippingAmount = $source->shippingAmount;
        $order->shippingAmountNet = $source->shippingAmountNet;
        $order->comment = $source->sComment;
        $order->deviceType = $source->deviceType;

        return $order;
    }

    /**
     * @param OrderSource $source
     * @throws \InvalidArgumentException
     * @return OrderCheckoutSource
     */
    private function testSource(OrderSource $source): OrderCheckoutSource
    {
        if (!$source instanceof OrderCheckoutSource) {
            throw new \InvalidArgumentException('Invalid source class provided');
        }

        return $source;
    }
}
