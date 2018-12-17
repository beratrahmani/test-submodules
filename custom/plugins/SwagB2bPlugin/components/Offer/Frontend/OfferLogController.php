<?php declare(strict_types = 1);

namespace Shopware\B2B\Offer\Frontend;

use Shopware\B2B\AuditLog\Framework\AuditLogRepository;
use Shopware\B2B\AuditLog\Framework\AuditLogSearchStruct;
use Shopware\B2B\AuditLog\Framework\AuditLogValueOrderCommentEntity;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\Filter\EqualsFilter;
use Shopware\B2B\Common\Filter\NotEqualsFilter;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Offer\Framework\OfferAuditLogService;
use Shopware\B2B\Offer\Framework\OfferService;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OfferLogController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

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
     * @param OfferService $offerService
     * @param OrderContextRepository $orderContextRepository
     * @param AuditLogRepository $auditLogRepository
     * @param GridHelper $auditLogGridHelper
     * @param OfferAuditLogService $offerAuditLogService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        OfferService $offerService,
        OrderContextRepository $orderContextRepository,
        AuditLogRepository $auditLogRepository,
        GridHelper $auditLogGridHelper,
        OfferAuditLogService $offerAuditLogService,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->offerService = $offerService;
        $this->orderContextRepository = $orderContextRepository;
        $this->auditLogRepository = $auditLogRepository;
        $this->auditLogGridHelper = $auditLogGridHelper;
        $this->currencyService = $currencyService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function logAction(Request $request): array
    {
        $orderContextId = (int) $request->requireParam('orderContextId');

        $ownIdentity = $this->authenticationService
            ->getIdentity();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $searchStruct = new AuditLogSearchStruct();
        $searchStruct->filters = [new NotEqualsFilter(
            $this->auditLogRepository::TABLE_ALIAS,
            'log_type',
            AuditLogValueOrderCommentEntity::class
        )];

        $searchStruct->limit = PHP_INT_MAX;
        $searchStruct->orderBy = 'auditLog.id';
        $searchStruct->orderDirection = 'ASC';

        $this->auditLogGridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $logItems = $this->auditLogRepository
            ->fetchList(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct, $currencyContext);

        $totalCount = $this->auditLogRepository
            ->fetchTotalCount(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct);

        $maxPage = $this->auditLogGridHelper
            ->getMaxPage($totalCount);

        $currentPage = (int) $request->getParam('page', 1);

        $orderGridState = $this->auditLogGridHelper
            ->getGridState($request, $searchStruct, $logItems, $maxPage, $currentPage);

        return [
            'gridState' => $orderGridState,
            'orderContextId' => $orderContextId,
            'ownIdentity' => $ownIdentity,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function commentListAction(Request $request): array
    {
        $orderContextId = (int) $request->requireParam('orderContextId');

        $ownIdentity = $this->authenticationService
            ->getIdentity();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $searchStruct = new AuditLogSearchStruct();
        $searchStruct->filters = [new EqualsFilter(
            $this->auditLogRepository::TABLE_ALIAS,
            'log_type',
            AuditLogValueOrderCommentEntity::class
        )];

        $searchStruct->limit = PHP_INT_MAX;
        $searchStruct->orderBy = 'auditLog.id';
        $searchStruct->orderDirection = 'ASC';

        $this->auditLogGridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $logItems = $this->auditLogRepository
            ->fetchList(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct, $currencyContext);

        $totalCount = $this->auditLogRepository
            ->fetchTotalCount(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct);

        $maxPage = $this->auditLogGridHelper
            ->getMaxPage($totalCount);

        $currentPage = (int) $request->getParam('page', 1);

        $orderGridState = $this->auditLogGridHelper
            ->getGridState($request, $searchStruct, $logItems, $maxPage, $currentPage);

        return [
            'gridState' => $orderGridState,
            'orderContextId' => $orderContextId,
            'ownIdentity' => $ownIdentity,
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function commentAction(Request $request)
    {
        $orderContextId = (int) $request->requireParam('orderContextId');
        $ownIdentity = $this->authenticationService->getIdentity();

        $orderContext = $this->orderContextRepository->fetchOneOrderContextById($orderContextId, $ownIdentity->getOwnershipContext());

        $this->offerService
            ->saveComment((string) $request->getParam('comment'), $orderContext, $ownIdentity, false);

        throw new B2bControllerForwardException('commentList', null, null, [
            'orderContextId' => $orderContextId,
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newCommentAction(Request $request): array
    {
        return ['orderContextId' => $request->getParam('orderContextId')];
    }
}
