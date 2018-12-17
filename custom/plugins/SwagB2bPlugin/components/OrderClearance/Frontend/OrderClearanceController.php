<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\B2bControllerRedirectException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceRepositoryInterface;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceSearchStruct;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OrderClearanceController
{
    /**
     * @var OrderClearanceService
     */
    private $orderClearanceService;

    /**
     * @var OrderClearanceRepositoryInterface
     */
    private $orderClearanceRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param AuthenticationService $authenticationService
     * @param OrderClearanceService $orderClearanceService
     * @param OrderClearanceRepositoryInterface $orderClearanceRepository
     * @param GridHelper $gridHelper
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderClearanceService $orderClearanceService,
        OrderClearanceRepositoryInterface $orderClearanceRepository,
        GridHelper $gridHelper,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->orderClearanceRepository = $orderClearanceRepository;
        $this->gridHelper = $gridHelper;
        $this->orderClearanceService = $orderClearanceService;
        $this->currencyService = $currencyService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $identity = $this->authenticationService->getIdentity();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $searchStruct = new OrderClearanceSearchStruct();

        $this->gridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $orderClearance = $this->orderClearanceService
            ->fetchAllOrderClearances($identity, $searchStruct, $currencyContext);

        $totalCount = $this->orderClearanceRepository
            ->fetchTotalCount($identity, $searchStruct);

        $maxPage = $this->gridHelper
            ->getMaxPage($totalCount);

        $currentPage = (int) $request->getParam('page', 1);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $orderClearance, $maxPage, $currentPage);

        return [
            'gridState' => $gridState,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        $orderContextId = (int) $request->requireParam('orderContextId');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->authenticationService->getIdentity();

        $orderContext = $this->orderClearanceRepository
            ->fetchOneByOrderContextId($orderContextId, $currencyContext, $identity->getOwnershipContext());

        return [
            'list' => $orderContext->list,
            'orderContext' => $orderContext,
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();

        $orderContextId = (int) $request
            ->requireParam('orderContextId');

        $identity = $this->authenticationService
            ->getIdentity();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $this->orderClearanceService
            ->deleteOrder($identity, $orderContextId, $currencyContext);

        throw new B2bControllerForwardException('grid');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function acceptAction(Request $request): array
    {
        $request->checkPost('grid');

        $orderContextId = (int) $request
            ->requireParam('orderContextId');

        $identity = $this->authenticationService
            ->getIdentity();

        $currencyContext = $this->currencyService->createCurrencyContext();

        $this->orderClearanceService
            ->acceptOrder($identity, $orderContextId, $currencyContext);

        throw new B2bControllerRedirectException('confirm', 'checkout');
    }

    /**
     * @param Request $request
     */
    public function stopAcceptanceAction(Request $request)
    {
        $request->checkPost();

        $this->orderClearanceService
            ->stopAcceptance();

        throw new B2bControllerRedirectException('index', 'b2border');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function declineAction(Request $request): array
    {
        $orderContextId = (int) $request
            ->requireParam('orderContextId');
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->authenticationService->getIdentity();

        $order = $this->orderClearanceRepository
            ->fetchOneByOrderContextId($orderContextId, $currencyContext, $identity->getOwnershipContext());

        return [
            'order' => $order,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function declineOrderAction(Request $request): array
    {
        $request->checkPost('grid');

        $orderContextId = (int) $request
            ->requireParam('orderContextId');

        $comment = $request->getParam('comment', '');

        $identity = $this->authenticationService
            ->getIdentity();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $this->orderClearanceService
            ->declineOrder($identity, $orderContextId, $comment, $currencyContext);

        return [];
    }
}
