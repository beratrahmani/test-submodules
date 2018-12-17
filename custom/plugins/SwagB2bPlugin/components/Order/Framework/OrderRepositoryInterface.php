<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface OrderRepositoryInterface extends GridRepository
{
    /**
     * @param OwnershipContext $ownershipContext
     * @param OrderSearchStruct $orderSearchStruct
     * @param CurrencyContext $currencyContext
     * @return OrderEntity[]
     */
    public function fetchLists(OwnershipContext $ownershipContext, OrderSearchStruct $orderSearchStruct, CurrencyContext $currencyContext): array;

    /**
     * @param OwnershipContext $ownershipContext
     * @param OrderSearchStruct $orderSearchStruct
     * @return int
     */
    public function fetchTotalCount(OwnershipContext $ownershipContext, OrderSearchStruct $orderSearchStruct): int;

    /**
     * @param int $orderContextId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return OrderEntity
     */
    public function fetchOrderById(int $orderContextId, CurrencyContext $currencyContext, OwnershipContext $ownershipContext): OrderEntity;

    /**
     * @param int $listId
     * @throws NotFoundException
     * @return OrderContext
     */
    public function fetchOrderContextByListId(int $listId): OrderContext;

    /**
     * @param int $orderId
     * @return int
     */
    public function fetchAuthIdFromOrderById(int $orderId): int;
}
