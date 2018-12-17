<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Frontend;

use Shopware\B2B\Budget\Framework\BudgetService;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\EmptyForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\OrderList\Framework\OrderListCrudService;
use Shopware\B2B\OrderList\Framework\OrderListRepository;
use Shopware\B2B\OrderList\Framework\OrderListSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class OrderListController
{
    /**
     * @var OrderListRepository
     */
    private $orderListRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderListCrudService
     */
    private $orderListCrudService;

    /**
     * @var BudgetService
     */
    private $budgetService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param AuthenticationService $authenticationService
     * @param OrderListRepository $orderListRepository
     * @param OrderListCrudService $orderListCrudService
     * @param GridHelper $orderListGridHelper
     * @param BudgetService $budgetService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderListRepository $orderListRepository,
        OrderListCrudService $orderListCrudService,
        GridHelper $orderListGridHelper,
        BudgetService $budgetService,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->orderListRepository = $orderListRepository;
        $this->orderListCrudService = $orderListCrudService;
        $this->gridHelper = $orderListGridHelper;
        $this->budgetService = $budgetService;
        $this->currencyService = $currencyService;
    }

    public function indexAction()
    {
        //nth
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $ownershipContext = $this->getOwnershipContext();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $searchStruct = new OrderListSearchStruct();

        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $orderLists = $this->orderListRepository
            ->fetchList($searchStruct, $ownershipContext, $currencyContext);

        $totalCount = $this->orderListRepository
            ->fetchTotalCount($searchStruct, $ownershipContext);

        $maxPage = $this->gridHelper->getMaxPage($totalCount);

        $currentPage = (int) $this->gridHelper->getCurrentPage($request);

        $gridState = $this->gridHelper->getGridState($request, $searchStruct, $orderLists, $maxPage, $currentPage);

        return ['gridState' => $gridState];
    }

    /**
     * @return array
     */
    public function newAction(): array
    {
        $validationResponse = $this->gridHelper->getValidationResponse('orderList');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        return array_merge([
            'isNew' => true,
            'budgets' => $this->budgetService
                ->getUserSelectableBudgetsWithStatus($ownershipContext, PHP_INT_MAX, $currencyContext),
        ], $validationResponse);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function createAction(Request $request)
    {
        $request->checkPost();

        $post = $request->getPost();

        $serviceRequest = $this->orderListCrudService
            ->createNewRecordRequest($post);

        $identity = $this->authenticationService
            ->getIdentity();

        try {
            $orderList = $this->orderListCrudService
                ->create($serviceRequest, $identity->getOwnershipContext());
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
            throw new B2bControllerForwardException('new');
        }

        throw new B2bControllerForwardException('detail', null, null, ['orderlist' => $orderList->id]);
    }

    /**
     * @param Request $request
     * @throws \Exception
     * @return array
     */
    public function createAjaxAction(Request $request)
    {
        $request->checkPost();

        $post = $request->getPost();

        $serviceRequest = $this->orderListCrudService
            ->createNewRecordRequest($post);

        $identity = $this->authenticationService
            ->getIdentity();

        try {
            $orderList = $this->orderListCrudService
                ->create($serviceRequest, $identity->getOwnershipContext());
        } catch (ValidationException $e) {
            return [];
        }

        return [
            'orderListId' => $orderList->id,
            'name' => $orderList->name,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        $orderListId = (int) $request->requireParam('orderlist');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getOwnershipContext();

        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        return ['orderList' => $orderList];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function editAction(Request $request): array
    {
        $orderListId = (int) $request->requireParam('orderlist');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getOwnershipContext();

        $orderList = $this->orderListRepository
            ->fetchOneById($orderListId, $currencyContext, $ownershipContext);

        $validationResponse = $this->gridHelper->getValidationResponse('orderList');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        return array_merge([
            'orderList' => $orderList,
            'budgets' => $this->budgetService
                ->getUserSelectableBudgetsWithStatus($ownershipContext, PHP_INT_MAX, $currencyContext),
        ], $validationResponse);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getOwnershipContext();

        $orderListId = (int) $request->requireParam('orderlist');

        $post = $request->getPost();
        $post['id'] = $orderListId;

        $serviceRequest = $this->orderListCrudService
            ->createExistingRecordRequest($post);

        try {
            $orderList = $this->orderListCrudService->update($serviceRequest, $currencyContext, $ownershipContext);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
            throw new B2bControllerForwardException('edit', null, null, ['orderlist' => $orderListId]);
        }

        throw new B2bControllerForwardException('edit', null, null, ['orderlist' => $orderList->id]);
    }

    /**
     * @param Request $request
     */
    public function produceCartAction(Request $request)
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $orderListId = (int) $request->requireParam('orderlist');

        $this->orderListCrudService
            ->produceCart($orderListId, $currencyContext, $this->getOwnershipContext());

        throw new EmptyForwardException();
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function duplicateAction(Request $request)
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $id = (int) $request->requireParam('id');

        $identity = $this->authenticationService
            ->getIdentity();

        $this->orderListCrudService
            ->duplicate($id, $identity->getOwnershipContext(), $currencyContext);

        throw new B2bControllerForwardException('grid');
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getOwnershipContext();

        $serviceRequest = $this->orderListCrudService
            ->createExistingRecordRequest($request->getPost());

        try {
            $this->orderListCrudService
                ->remove($serviceRequest, $currencyContext, $ownershipContext);
        } catch (NotFoundException $e) {
            // nth
        }

        throw new B2bControllerForwardException('grid');
    }

    /**
     * @internal
     * @return OwnershipContext
     */
    protected function getOwnershipContext(): OwnershipContext
    {
        return $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();
    }
}
