<?php declare(strict_types = 1);

namespace Shopware\B2B\OrderList\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceRepository;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceSearchStruct;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceService;
use Shopware\B2B\Order\Framework\OrderLineItemReferenceCrudService;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OrderListLineItemReferenceController
{
    /**
     * @var LineItemReferenceService
     */
    private $lineItemReferenceService;

    /**
     * @var OrderLineItemReferenceCrudService
     */
    private $lineItemReferenceCrudService;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var LineItemReferenceRepository
     */
    private $lineItemReferenceRepository;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param OrderListRepository $orderListRepository
     * @param LineItemListRepository $lineItemListRepository
     * @param LineItemReferenceRepository $lineItemReferenceRepository
     * @param GridHelper $gridHelper
     * @param OrderLineItemReferenceCrudService $lineItemReferenceCrudService
     * @param LineItemReferenceService $lineItemReferenceService
     * @param CurrencyService $currencyService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        OrderListRepository $orderListRepository,
        LineItemListRepository $lineItemListRepository,
        LineItemReferenceRepository $lineItemReferenceRepository,
        GridHelper $gridHelper,
        OrderLineItemReferenceCrudService $lineItemReferenceCrudService,
        LineItemReferenceService $lineItemReferenceService,
        CurrencyService $currencyService,
        AuthenticationService $authenticationService
    ) {
        $this->orderListRepository = $orderListRepository;
        $this->lineItemListRepository = $lineItemListRepository;
        $this->lineItemReferenceRepository = $lineItemReferenceRepository;
        $this->gridHelper = $gridHelper;
        $this->lineItemReferenceCrudService = $lineItemReferenceCrudService;
        $this->lineItemReferenceService = $lineItemReferenceService;
        $this->currencyService = $currencyService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $orderListId = (int) $request->requireParam('orderlist');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $listId = $orderList->listId;

        $lineItemList = $this->lineItemListRepository
            ->fetchOneListById($listId, $currencyContext, $ownershipContext);

        $searchStruct = new LineItemReferenceSearchStruct();

        $this->gridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $searchStruct->offset = 0;
        $searchStruct->limit = PHP_INT_MAX;

        $this->lineItemReferenceService->mapCustomOrderNumbers($lineItemList->references, $ownershipContext);

        $lineItemList = $this->lineItemReferenceService
            ->fetchLineItemListProductNames($lineItemList);

        $totalCount = $this->lineItemReferenceRepository
            ->fetchTotalCount($listId, $searchStruct);

        $currentPage = $this->gridHelper
            ->getCurrentPage($request);

        $maxPage = $this->gridHelper
            ->getMaxPage($totalCount);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $lineItemList->references, $currentPage, $maxPage);

        $validationResponse = $this->gridHelper
            ->getValidationResponse('lineItemReference');

        return array_merge(
            [
                'gridState' => $gridState,
                'listId' => $listId,
                'orderList' => $orderList,
            ],
            $this->getHeaderData($lineItemList),
            $validationResponse
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newAction(Request $request): array
    {
        $orderListId = (int) $request->requireParam('orderlist');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $validationResponse = $this->gridHelper
            ->getValidationResponse('lineItemReference');

        return array_merge(
            ['orderList' => $orderList],
            $validationResponse
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function createAction(Request $request)
    {
        try {
            $this->createLineItemReferenceFromRequest($request);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
            throw new B2bControllerForwardException('new', null, null, ['orderlist' => $request->requireParam('orderlist')]);
        }

        throw new B2bControllerForwardException('index', null, null, ['orderlist' => $request->requireParam('orderlist')]);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $orderListId = (int) $request->requireParam('orderlist');
        $listId = (int) $request->requireParam('listId');

        $crudRequest = $this->lineItemReferenceCrudService
            ->createUpdateCrudRequest($request->getPost());

        try {
            $this->lineItemReferenceCrudService
                ->updateLineItem($listId, $crudRequest, $currencyContext, $ownershipContext);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('index', null, null, ['orderlist' => $orderListId]);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $orderListId = (int) $request->getParam('orderlist');
        $lineItemId = (int) $request->getParam('lineItemId');

        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $this->lineItemReferenceCrudService
            ->deleteLineItem($orderList->listId, $lineItemId, $currencyContext, $ownershipContext);

        throw new B2bControllerForwardException('index', null, null, ['id' => $orderList->id]);
    }

    /**
     * @param Request $request
     */
    public function sortAction(Request $request)
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $orderListId = (int) $request->requireParam('orderlist');
        $itemIdOne = (int) $request->requireParam('itemIdOne');
        $itemIdTwo = (int) $request->requireParam('itemIdTwo');

        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $this->lineItemReferenceCrudService
                ->flipLineItemSorting($itemIdOne, $itemIdTwo);

        throw new B2bControllerForwardException('index', null, null, ['id' => $orderList->id]);
    }

    /**
     * @internal
     * @param Request $request
     * @return LineItemReference
     */
    protected function createLineItemReferenceFromRequest(Request $request): LineItemReference
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $orderListId = (int) $request->requireParam('orderlist');

        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $crudRequest = $this->lineItemReferenceCrudService
            ->createCreateCrudRequest($request->getPost());

        return $this->lineItemReferenceCrudService
            ->addLineItem($orderList->listId, $crudRequest, $currencyContext, $ownershipContext);
    }

    /**
     * @internal
     * @param LineItemList $list
     * @return array
     */
    protected function getHeaderData(LineItemList $list): array
    {
        return [
            'itemCount' => count($list->references),
            'amountNet' => $list->amountNet,
            'amount' => $list->amount,
        ];
    }
}
