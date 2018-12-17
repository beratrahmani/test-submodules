<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Shopware\B2B\Order\Framework\OrderStatusInterpreterServiceInterface;

class OrderStatusInterpreterService implements OrderStatusInterpreterServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function isOpen(int $statusId): bool
    {
        return !$this->isCancelled($statusId) && $statusId >= 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isCancelled(int $statusId): bool
    {
        return $statusId === -1 || $statusId === 4 || $statusId === -3;
    }
}
