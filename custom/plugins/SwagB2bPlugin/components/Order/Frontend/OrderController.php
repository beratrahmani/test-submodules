<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Frontend;

use Shopware\B2B\AuditLog\Framework\AuditLogRepository;
use Shopware\B2B\AuditLog\Framework\AuditLogSearchStruct;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\Order\Framework\OrderRepositoryInterface;
use Shopware\B2B\Order\Framework\OrderSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OrderController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var GridHelper
     */
    private $orderGridHelper;

    /**
     * @var AuditLogRepository
     */
    private $auditLogRepository;

    /**
     * @var GridHelper
     */
    private $auditLogGridHelper;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param AuthenticationService $authenticationService
     * @param OrderRepositoryInterface $orderRepository
     * @param GridHelper $orderGridHelper
     * @param AuditLogRepository $auditLogRepository
     * @param GridHelper $auditLogGridHelper
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OrderRepositoryInterface $orderRepository,
        GridHelper $orderGridHelper,
        AuditLogRepository $auditLogRepository,
        GridHelper $auditLogGridHelper,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->orderRepository = $orderRepository;
        $this->orderGridHelper = $orderGridHelper;
        $this->auditLogRepository = $auditLogRepository;
        $this->auditLogGridHelper = $auditLogGridHelper;
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
        $orderCredentials = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $searchStruct = new OrderSearchStruct();

        $this->orderGridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $order = $this->orderRepository
            ->fetchLists($orderCredentials, $searchStruct, $currencyContext);

        $totalCount = $this->orderRepository
            ->fetchTotalCount($orderCredentials, $searchStruct);

        $maxPage = $this->orderGridHelper
            ->getMaxPage($totalCount);

        $currentPage = (int) $request->getParam('page', 1);

        $orderGridState = $this->orderGridHelper
            ->getGridState($request, $searchStruct, $order, $maxPage, $currentPage);

        return [
            'orderGrid' => $orderGridState,
            'message' => $request->getParam('message'),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $orderContextId = (int) $request->requireParam('orderContextId');

        $order = $this->orderRepository->fetchOrderById($orderContextId, $currencyContext, $ownershipContext);

        return [
            'list' => $order->list,
            'orderContext' => $order,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function logAction(Request $request): array
    {
        $orderContextId = (int) $request->requireParam('orderContextId');

        $searchStruct = new AuditLogSearchStruct();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $this->orderGridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $logItems = $this->auditLogRepository
            ->fetchList(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct, $currencyContext);
        
        $totalCount = $this->auditLogRepository
            ->fetchTotalCount(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct);

        $maxPage = $this->orderGridHelper
            ->getMaxPage($totalCount);

        $currentPage = (int) $request->getParam('page', 1);

        $orderGridState = $this->auditLogGridHelper
            ->getGridState($request, $searchStruct, $logItems, $maxPage, $currentPage);

        return [
            'gridState' => $orderGridState,
            'orderContextId' => $orderContextId,
        ];
    }
}
