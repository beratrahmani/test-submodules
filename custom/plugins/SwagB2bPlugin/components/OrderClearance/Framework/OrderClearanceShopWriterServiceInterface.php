<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Framework;

interface OrderClearanceShopWriterServiceInterface
{
    /**
     * @param OrderClearanceEntity $orderClearance
     */
    public function sendToClearance(OrderClearanceEntity $orderClearance);

    public function stopOrderClearance();
}
