<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

interface OrderStatusInterpreterServiceInterface
{
    /**
     * Indicate if an order is ready to be processed
     *
     * @param int $statusId
     * @return bool
     */
    public function isOpen(int $statusId): bool;

    /**
     * Indicate whether the order was cancelled
     *
     * @param int $statusId
     * @return bool
     */
    public function isCancelled(int $statusId): bool;
}
