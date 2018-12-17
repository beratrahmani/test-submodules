<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface ShopOrderRepositoryInterface
{
    /**
     * @param string $orderNumber
     * @param int $authId
     */
    public function setOrderIdentity(string $orderNumber, int $authId);

    /**
     * @param string $orderNumber
     * @param int $debtorId
     */
    public function setOrderToShopOwnerUser(string $orderNumber, int $debtorId);

    /**
     * @param int $orderContextId
     * @param int $statusId
     * @param OwnershipContext $ownershipContext
     */
    public function updateStatus(int $orderContextId, int $statusId, OwnershipContext $ownershipContext);
}
