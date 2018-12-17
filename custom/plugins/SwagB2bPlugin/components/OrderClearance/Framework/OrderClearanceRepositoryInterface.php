<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Framework;

use Shopware\B2B\Common\Controller\GridRepository;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface OrderClearanceRepositoryInterface extends GridRepository
{
    const STATUS_ORDER_CLEARANCE = -2;

    const STATUS_ORDER_OPEN = 0;

    const STATUS_ORDER_DENIED = -3;

    /**
     * @param int $orderContextId
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return OrderClearanceEntity
     */
    public function fetchOneByOrderContextId(int $orderContextId, CurrencyContext $currencyContext, OwnershipContext $ownershipContext): OrderClearanceEntity;

    /**
     * @param Identity $identity
     * @param OrderClearanceSearchStruct $searchStruct
     * @param CurrencyContext $currencyContext
     * @return OrderClearanceEntity[]
     */
    public function fetchAllOrderClearances(Identity $identity, OrderClearanceSearchStruct $searchStruct, CurrencyContext $currencyContext): array;

    /**
     * @param Identity $identity
     * @param OrderClearanceSearchStruct $searchStruct
     * @return int
     */
    public function fetchTotalCount(Identity $identity, OrderClearanceSearchStruct $searchStruct): int;

    /**
     * @param Identity $identity
     * @param int $orderContextId
     * @return bool
     */
    public function belongsOrderContextIdToDebtor(Identity $identity, int $orderContextId): bool;

    /**
     * @param int $orderContextId
     * @param string $comment
     * @param OwnershipContext $ownershipContext
     */
    public function declineOrder(int $orderContextId, string $comment, OwnershipContext $ownershipContext);

    /**
     * @param int $orderContextId
     * @param OwnershipContext $ownershipContext
     */
    public function sendToOrderClearance(int $orderContextId, OwnershipContext $ownershipContext);

    /**
     * @param int $orderContextId
     * @param OwnershipContext $ownershipContext
     */
    public function deleteOrder(int $orderContextId, OwnershipContext $ownershipContext);
}
