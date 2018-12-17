<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Frontend;

use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceRepository;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceSearchStruct;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceService;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextService;
use Shopware\B2B\Order\Framework\OrderLineItemReferenceCrudService;
use Shopware\B2B\Order\Framework\OrderRepositoryInterface;
use Shopware\B2B\OrderList\Framework\OrderListRelationRepositoryInterface;
use Shopware\B2B\Shop\Framework\OrderRelationServiceInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderLineItemReferenceController
{
    /**
     * @var LineItemReferenceRepository
     */
    private $repository;

    /**
     * @var GridHelper
     */
    private $grid;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var OrderRelationServiceInterface
     */
    private $orderRelationService;

    /**
     * @var OrderLineItemReferenceCrudService
     */
    private $lineItemReferenceCrudService;

    /**
     * @var OrderContextService
     */
    private $orderContextService;

    /**
     * @var OrderListRelationRepositoryInterface
     */
    private $orderListRelationRepository;

    /**
     * @var LineItemReferenceService
     */
    private $lineItemReferenceService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param LineItemReferenceRepository $repository
     * @param GridHelper $grid
     * @param OrderRepositoryInterface $orderRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param OrderRelationServiceInterface $orderRelationService
     * @param OrderLineItemReferenceCrudService $lineItemReferenceCrudService
     * @param OrderContextService $orderContextService
     * @param OrderListRelationRepositoryInterface $orderListRelationRepository
     * @param LineItemReferenceService $lineItemReferenceService
     * @param CurrencyService $currencyService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        LineItemReferenceRepository $repository,
        GridHelper $grid,
        OrderRepositoryInterface $orderRepository,
        AddressRepositoryInterface $addressRepository,
        OrderRelationServiceInterface $orderRelationService,
        OrderLineItemReferenceCrudService $lineItemReferenceCrudService,
        OrderContextService $orderContextService,
        OrderListRelationRepositoryInterface $orderListRelationRepository,
        LineItemReferenceService $lineItemReferenceService,
        CurrencyService $currencyService,
        AuthenticationService $authenticationService
    ) {
        $this->repository = $repository;
        $this->grid = $grid;
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->orderRelationService = $orderRelationService;
        $this->lineItemReferenceCrudService = $lineItemReferenceCrudService;
        $this->orderContextService = $orderContextService;
        $this->orderListRelationRepository = $orderListRelationRepository;
        $this->lineItemReferenceService = $lineItemReferenceService;
        $this->currencyService = $currencyService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function masterDataAction(Request $request): array
    {
        $orderContextId = (int) $request->requireParam('orderContextId');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->authenticationService->getIdentity();

        $order = $this->orderRepository
            ->fetchOrderById($orderContextId, $currencyContext, $identity->getOwnershipContext());

        $billingAddress = $this->addressRepository
            ->fetchOneById($order->billingAddressId, $identity, 'billing');

        $shippingAddress = $this->addressRepository
            ->fetchOneById($order->shippingAddressId, $identity, 'shipping');

        $paymentName = null;
        if ($order->paymentId) {
            $paymentName = $this->orderRelationService->getPaymentNameForId($order->paymentId);
        }

        $shippingName = null;
        try {
            $shippingName = $this->orderRelationService->getShippingNameForId($order->shippingId);
        } catch (NotFoundException $e) {
            //nth
        }

        try {
            $orderInfo = $this->orderListRelationRepository
                ->fetchOrderListNameForListId($order->listId, $identity->getOwnershipContext());
        } catch (NotFoundException $e) {
            $orderInfo = null;
        }

        return array_merge(
            $this->getHeaderData($order->list, $order),
            [
                'orderContext' => $order,
                'comment' => $order->comment,
                'requestedDeliveryDate' => $order->requestedDeliveryDate,
                'billingAddress' => $billingAddress,
                'shippingAddress' => $shippingAddress,
                'paymentName' => $paymentName,
                'shippingName' => $shippingName,
                'orderInfo' => $orderInfo,
        ]
        );
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function updateMasterDataAction(Request $request)
    {
        $request->checkPost();

        $orderContextId = (int) $request->requireParam('orderContextId');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $order = $this->orderRepository
            ->fetchOrderById($orderContextId, $currencyContext, $ownershipContext);

        if ($request->hasParam('comment')) {
            $this->orderContextService
                ->saveComment((string) $request->getParam('comment'), $order);
        }

        if ($request->hasParam('orderReference')) {
            $this->orderContextService
                ->saveOrderReference((string) $request->getParam('orderReference'), $order);
        }

        if ($request->hasParam('requestedDeliveryDate')) {
            $this->orderContextService
                ->saveRequestedDeliveryDate((string) $request->getParam('requestedDeliveryDate'), $order);
        }

        throw new B2bControllerForwardException('masterData', null, null, ['orderContextId' => $orderContextId]);
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function updateOrderReferenceAction(Request $request)
    {
        $request->checkPost();

        $orderContextId = (int) $request->requireParam('orderContextId');
        $orderReference = (string) $request->requireParam('orderReference');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $order = $this->orderRepository
            ->fetchOrderById($orderContextId, $currencyContext, $ownershipContext);

        $this->orderContextService
            ->saveOrderReference($orderReference, $order);

        throw new B2bControllerForwardException('masterData', null, null, ['orderContextId' => $orderContextId]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function listAction(Request $request): array
    {
        $orderContextId = (int) $request->requireParam('orderContextId');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $order = $this->orderRepository
            ->fetchOrderById($orderContextId, $currencyContext, $ownershipContext);

        $searchStruct = new LineItemReferenceSearchStruct();

        $this->grid->extractSearchDataInStoreFront($request, $searchStruct);

        $searchStruct->offset = 0;
        $searchStruct->limit = PHP_INT_MAX;

        $items = $this->lineItemReferenceService
            ->fetchLineItemsReferencesWithProductNames($order->listId, $searchStruct, $ownershipContext);

        $items = $this->addOrderListToPositionsFromListIdAndNumber($order->listId, $items, $ownershipContext);

        $totalCount = $this->repository
            ->fetchTotalCount($order->listId, $searchStruct);

        $currentPage = $this->grid
            ->getCurrentPage($request);

        $maxPage = $this->grid
            ->getMaxPage($totalCount);

        $gridState = $this->grid
            ->getGridState($request, $searchStruct, $items, $currentPage, $maxPage);

        $validationResponse = $this->grid
            ->getValidationResponse('lineItemReference');

        return array_merge(
            $this->getHeaderData($order->list, $order),
            [
                'itemGrid' => $gridState,
                'listId' => $order->listId,
                'orderContext' => $order,
            ],
            $validationResponse
        );
    }

    /**
     * @internal
     * @param int $listId
     * @param LineItemReference[] $references
     * @param OwnershipContext $ownershipContext
     * @return LineItemReference[]
     */
    protected function addOrderListToPositionsFromListIdAndNumber(
        int $listId,
        array $references,
        OwnershipContext $ownershipContext
    ): array {
        foreach ($references as $reference) {
            try {
                $reference->orderList = $this->orderListRelationRepository
                    ->fetchOrderListNameForPositionNumber($listId, $reference->referenceNumber, $ownershipContext);
            } catch (NotFoundException $e) {
                // nth
            }
        }

        return $references;
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function saveCommentAction(Request $request)
    {
        $request->checkPost();

        $comment = (string) $request->requireParam('comment');
        $orderContextId = (int) $request->requireParam('orderContextId');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $currencyContext = $this->currencyService->createCurrencyContext();

        $order = $this->orderRepository
            ->fetchOrderById($orderContextId, $currencyContext, $ownershipContext);

        $this->orderContextService
            ->saveComment($comment, $order);

        throw new B2bControllerForwardException('masterData', null, null, ['orderContextId' => $orderContextId]);
    }

    /**
     * @param Request $request
     */
    public function updateLineItemAction(Request $request)
    {
        $request->checkPost();

        $orderContextId = (int) $request->requireParam('orderContextId');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $order = $this->orderRepository
            ->fetchOrderById($orderContextId, $currencyContext, $ownershipContext);

        $crudRequest = $this->lineItemReferenceCrudService
            ->createUpdateCrudRequest($request->getPost());

        try {
            $this->lineItemReferenceCrudService
                ->updateLineItem($order->listId, $crudRequest, $currencyContext, $ownershipContext);
        } catch (ValidationException $e) {
            $this->grid->pushValidationException($e);
        }

        throw new B2bControllerForwardException('list', null, null, ['orderContextId' => $orderContextId]);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function removeLineItemAction(Request $request)
    {
        $request->checkPost();

        $lineItemId = (int) $request->requireParam('lineItemId');
        $orderContextId = (int) $request->requireParam('orderContextId');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $order = $this->orderRepository
            ->fetchOrderById($orderContextId, $currencyContext, $ownershipContext);

        $this->lineItemReferenceCrudService
            ->deleteLineItemFromOrder($order->listId, $lineItemId, $order, $currencyContext, $ownershipContext);

        throw new B2bControllerForwardException('list', null, null, ['orderContextId' => $orderContextId]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newAction(Request $request): array
    {
        $validationResponse = $this->grid
            ->getValidationResponse('lineItemReference');

        return array_merge(
            ['orderContextId' => (int) $request->requireParam('orderContextId')],
            $validationResponse
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function createAction(Request $request)
    {
        $request->checkPost();

        $orderContextId = (int) $request->requireParam('orderContextId');

        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $orderContext = $this->orderRepository->fetchOrderById($orderContextId, $currencyContext, $ownershipContext);

        $crudRequest = $this->lineItemReferenceCrudService
            ->createCreateCrudRequest($request->getPost());

        try {
            $this->lineItemReferenceCrudService
                ->addLineItem($orderContext->listId, $crudRequest, $currencyContext, $ownershipContext);
        } catch (ValidationException $e) {
            $this->grid->pushValidationException($e);
            throw new B2bControllerForwardException('new', null, null, ['orderContextId' => $orderContextId]);
        }

        throw new B2bControllerForwardException('list', null, null, ['orderContextId' => $orderContextId]);
    }

    /**
     * @internal
     * @param LineItemList $list
     * @param OrderContext $orderContext
     * @return array
     */
    protected function getHeaderData(LineItemList $list, OrderContext $orderContext): array
    {
        return [
            'itemCount' => count($list->references),
            'amountNet' => $list->amountNet,
            'amount' => $list->amount,
            'createdAt' => $orderContext->createdAt,
            'orderNumber' => $orderContext->orderNumber,
            'state' => $orderContext->status,
            'stateId' => $orderContext->statusId,
        ];
    }
}
