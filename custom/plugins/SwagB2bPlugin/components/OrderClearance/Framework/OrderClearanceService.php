<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogValueBasicEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueDiffEntity;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartService;
use Shopware\B2B\Common\Repository\NotAllowedRecordException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\Order\Framework\OrderAuditLogService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class OrderClearanceService
{
    /**
     * @var OrderClearanceRepositoryInterface
     */
    private $orderClearanceRepository;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var OrderAuditLogService
     */
    private $auditLog;

    /**
     * @var OrderClearanceShopWriterServiceInterface
     */
    private $orderClearanceShopWriterService;

    /**
     * @param OrderClearanceRepositoryInterface $orderClearanceRepository
     * @param CartService $cartService
     * @param OrderAuditLogService $auditLog
     * @param OrderClearanceShopWriterServiceInterface $orderClearanceShopWriterService
     */
    public function __construct(
        OrderClearanceRepositoryInterface $orderClearanceRepository,
        CartService $cartService,
        OrderAuditLogService $auditLog,
        OrderClearanceShopWriterServiceInterface $orderClearanceShopWriterService
    ) {
        $this->orderClearanceRepository = $orderClearanceRepository;
        $this->cartService = $cartService;
        $this->auditLog = $auditLog;
        $this->orderClearanceShopWriterService = $orderClearanceShopWriterService;
    }

    /**
     * @param Identity $identity
     * @param OrderClearanceSearchStruct $searchStruct
     * @param CurrencyContext $currencyContext
     * @return OrderClearanceEntity[]
     */
    public function fetchAllOrderClearances(Identity $identity, OrderClearanceSearchStruct $searchStruct, CurrencyContext $currencyContext): array
    {
        $orders = $this->orderClearanceRepository
            ->fetchAllOrderClearances($identity, $searchStruct, $currencyContext);

        return array_values(array_filter($orders, function (OrderClearanceEntity $order) use ($identity): bool {
            try {
                $result = $this->checkAllowed($order, $identity, CartService::ENVIRONMENT_NAME_LISTING);
            } catch (NotAllowedRecordException $e) {
                return false;
            }

            $order->isClearable = $result->isClearable();

            return true;
        }));
    }

    /**
     * @param Identity $identity
     * @param int $orderContextId
     * @param CurrencyContext $currencyContext
     */
    public function acceptOrder(Identity $identity, int $orderContextId, CurrencyContext $currencyContext)
    {
        $this->checkOrderAccess($identity, $orderContextId, CartService::ENVIRONMENT_NAME_ORDER, $currencyContext);

        $orderClearance = $this->orderClearanceRepository
            ->fetchOneByOrderContextId($orderContextId, $currencyContext, $identity->getOwnershipContext());

        $this->orderClearanceShopWriterService
            ->sendToClearance($orderClearance);
    }

    public function stopAcceptance()
    {
        $this->orderClearanceShopWriterService
            ->stopOrderClearance();
    }

    /**
     * @param int $orderContextId
     * @param Identity $identity
     */
    public function createOrderAcceptedStatusChangeLogEntry(int $orderContextId, Identity $identity)
    {
        $auditLogValue = $this
            ->createAuditLogValue(
                (string) OrderClearanceRepositoryInterface::STATUS_ORDER_OPEN,
                (string) OrderClearanceRepositoryInterface::STATUS_ORDER_CLEARANCE
            );

        $this->auditLog->createStatusChangeAuditLog(
            $orderContextId,
            $auditLogValue,
            $identity
        );
    }

    /**
     * @param Identity $identity
     * @param int $orderContextId
     * @param string $comment
     * @param CurrencyContext $currencyContext
     */
    public function declineOrder(Identity $identity, int $orderContextId, string $comment, CurrencyContext $currencyContext)
    {
        $this->checkOrderAccess($identity, $orderContextId, CartService::ENVIRONMENT_NAME_MODIFY, $currencyContext);

        $auditLogValue = $this
            ->createAuditLogValue(
                (string) OrderClearanceRepositoryInterface::STATUS_ORDER_DENIED,
                (string) OrderClearanceRepositoryInterface::STATUS_ORDER_CLEARANCE
            );

        $this->auditLog->createStatusChangeAuditLog(
            $orderContextId,
            $auditLogValue,
            $identity
        );

        $this->orderClearanceRepository
            ->declineOrder($orderContextId, $comment, $identity->getOwnershipContext());
    }

    /**
     * @param Identity $identity
     * @param int $orderContextId
     * @param CurrencyContext $currencyContext
     * @throws \Shopware\B2B\Common\Repository\NotFoundException
     */
    public function deleteOrder(Identity $identity, int $orderContextId, CurrencyContext $currencyContext)
    {
        $this->checkOrderAccess($identity, $orderContextId, CartService::ENVIRONMENT_NAME_MODIFY, $currencyContext);

        $this->orderClearanceRepository->deleteOrder($orderContextId, $identity->getOwnershipContext());
    }

    /**
     * @internal
     * @param Identity $identity
     * @param int $orderContextId
     * @param string $environment
     * @param CurrencyContext $currencyContext
     */
    protected function checkOrderAccess(Identity $identity, int $orderContextId, string $environment, CurrencyContext $currencyContext)
    {
        $this->checkOrderContextIdBelongsToDebtor($identity, $orderContextId);

        $order = $this->orderClearanceRepository
            ->fetchOneByOrderContextId($orderContextId, $currencyContext, $identity->getOwnershipContext());

        $this->checkAllowed($order, $identity, $environment);
    }

    /**
     * @internal
     * @param OrderClearanceEntity $order
     * @param Identity $identity
     * @param string $environment
     * @throws NotAllowedRecordException
     * @return CartAccessResult
     */
    protected function checkAllowed(OrderClearanceEntity $order, Identity $identity, string $environment): CartAccessResult
    {
        $result = $this->cartService
            ->computeAccessibility($identity, $order, $environment);

        if ($result->hasErrors()) {
            $message = 'The given cart can not be reviewed by the current identity';
            throw new NotAllowedRecordException(
                $message,
                $message
            );
        }

        return $result;
    }

    /**
     * @internal
     * @param Identity $identity
     * @param int $orderContextId
     * @throws NotAllowedRecordException
     */
    protected function checkOrderContextIdBelongsToDebtor(Identity $identity, int $orderContextId)
    {
        if (!$this->orderClearanceRepository->belongsOrderContextIdToDebtor($identity, $orderContextId)) {
            throw new NotAllowedRecordException(
                'The given order context id: ' . $orderContextId . ' belongs to another debtor!',
                'The given order context id: %id% belongs to another debtor!',
                ['%id%' => $orderContextId]
            );
        }
    }

    /**
     * @internal
     * @param string $newValue
     * @param string $oldValue
     * @return AuditLogValueBasicEntity
     */
    protected function createAuditLogValue(string $newValue, string $oldValue): AuditLogValueBasicEntity
    {
        $auditLogValue = new AuditLogValueDiffEntity();
        $auditLogValue->newValue = $newValue;
        $auditLogValue->oldValue = $oldValue;

        return $auditLogValue;
    }
}
