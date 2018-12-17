<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework;

use Shopware\B2B\AuditLog\Framework\AuditLogValueOrderCommentEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueOrderReferenceEntity;
use Shopware\B2B\AuditLog\Framework\AuditLogValueRequestedDeliveryDateEntity;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderContextService
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderAuditLogService
     */
    private $orderClearanceAuditLogService;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var OrderCheckoutProviderInterface
     */
    private $checkoutProvider;

    /**
     * @var OrderContextShopWriterServiceInterface
     */
    private $orderContextShopWriterService;

    /**
     * @param AuthenticationService $authenticationService
     * @param OrderContextRepository $orderContextRepository
     * @param OrderAuditLogService $orderClearanceAuditLogService
     * @param OrderCheckoutProviderInterface $checkoutProvider
     * @param OrderContextShopWriterServiceInterface $orderContextShopWriterService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderContextRepository $orderContextRepository,
        OrderAuditLogService $orderClearanceAuditLogService,
        OrderCheckoutProviderInterface $checkoutProvider,
        OrderContextShopWriterServiceInterface $orderContextShopWriterService
    ) {
        $this->authenticationService = $authenticationService;
        $this->orderContextRepository = $orderContextRepository;
        $this->orderClearanceAuditLogService = $orderClearanceAuditLogService;
        $this->checkoutProvider = $checkoutProvider;
        $this->orderContextShopWriterService = $orderContextShopWriterService;
    }

    /**
     * @param string $comment
     * @param OrderContext $orderContext
     */
    public function saveComment(string $comment, OrderContext $orderContext)
    {
        if ($comment === $orderContext->comment) {
            return;
        }

        $identity = $this->authenticationService->getIdentity();

        $oldComment = $orderContext->comment;
        $orderContext->comment = $comment;

        $this->orderContextRepository->updateContext($orderContext);

        $auditLogValue = new AuditLogValueOrderCommentEntity();
        $auditLogValue->oldValue = $oldComment;
        $auditLogValue->newValue = $comment;

        $this->orderClearanceAuditLogService->createOrderClearanceComment(
            $auditLogValue,
            $this->orderContextRepository->fetchAuditLogReferencesByContextId($orderContext->id),
            $identity
        );
    }

    /**
     * @param string $orderReference
     * @param OrderContext $orderContext
     */
    public function saveOrderReference(
        string $orderReference,
        OrderContext $orderContext
    ) {
        if ((int) $orderContext->statusId !== -2) {
            throw new UnsupportedOrderStatusException(
                'You should not be able to update the order reference number of an open Order'
            );
        }

        if ($orderReference === $orderContext->orderReference) {
            return;
        }

        $identity = $this->authenticationService->getIdentity();

        $oldOrderReference = $orderContext->orderReference;
        $orderContext->orderReference = $orderReference;

        $this->orderContextRepository->updateContext($orderContext);

        $auditLogValue = new AuditLogValueOrderReferenceEntity();
        $auditLogValue->setData([
            'oldValue' => $oldOrderReference,
            'newValue' => $orderReference,
        ]);

        $this->orderClearanceAuditLogService->createOrderClearanceOrderReference(
            $auditLogValue,
            $this->orderContextRepository->fetchAuditLogReferencesByListId($orderContext->listId),
            $identity
        );
    }

    /**
     * @param string $requestedDeliveryDate
     * @param OrderContext $orderContext
     */
    public function saveRequestedDeliveryDate(
        string $requestedDeliveryDate,
        OrderContext $orderContext
    ) {
        if ((int) $orderContext->statusId !== -2) {
            throw new UnsupportedOrderStatusException(
                'You should not be able to update the order requested delivery date of an open Order'
            );
        }

        if ($requestedDeliveryDate === $orderContext->requestedDeliveryDate) {
            return;
        }

        $identity = $this->authenticationService->getIdentity();

        $oldRequestDeliveryDate = $orderContext->requestedDeliveryDate;
        $orderContext->requestedDeliveryDate = $requestedDeliveryDate;

        $this->orderContextRepository->updateContext($orderContext);

        $auditLogValue = new AuditLogValueRequestedDeliveryDateEntity();
        $auditLogValue->setData([
            'oldValue' => $oldRequestDeliveryDate,
            'newValue' => $requestedDeliveryDate,
        ]);

        $this->orderClearanceAuditLogService->createOrderClearanceRequestedDeliveryDate(
            $auditLogValue,
            $this->orderContextRepository->fetchAuditLogReferencesByListId($orderContext->id),
            $identity
        );
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param LineItemList $list
     * @param OrderSource $orderSource
     * @return OrderContext
     */
    public function createContextThroughCheckoutSource(
        OwnershipContext $ownershipContext,
        LineItemList $list,
        OrderSource $orderSource
    ): OrderContext {
        $orderContext = $this->checkoutProvider
            ->createOrder($orderSource);

        $orderContext->authId = $ownershipContext->authId;
        $orderContext->listId = $list->id;

        $this->orderContextRepository
            ->addOrderContext($orderContext);

        return $orderContext;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param LineItemList $list
     * @param OrderSource $orderSource
     * @param OrderContext $orderContext
     * @return OrderContext
     */
    public function updateOrderContextThroughCheckoutSource(
        OwnershipContext $ownershipContext,
        LineItemList $list,
        OrderSource $orderSource,
        OrderContext $orderContext
    ): OrderContext {
        $orderContext = $this->checkoutProvider
            ->updateOrder($orderSource, $orderContext);

        $orderContext->authId = $ownershipContext->authId;
        $orderContext->listId = $list->id;

        $this->orderContextRepository->updateContext($orderContext);

        return $orderContext;
    }

    /**
     * @param OrderContext $orderContext
     */
    public function extendCart(OrderContext $orderContext)
    {
        $this->orderContextShopWriterService
            ->extendCart($orderContext);
    }
}
