<?php declare(strict_types = 1);

namespace Shopware\B2B\Offer\Backend;

use Shopware\B2B\AuditLog\Framework\AuditLogRepository;
use Shopware\B2B\AuditLog\Framework\AuditLogSearchStruct;
use Shopware\B2B\AuditLog\Framework\AuditLogValueOrderCommentEntity;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\Filter\EqualsFilter;
use Shopware\B2B\Common\Filter\NotEqualsFilter;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Offer\Framework\OfferService;
use Shopware\B2B\Order\Framework\OrderBackendAuthenticationService;
use Shopware\B2B\Order\Framework\OrderContextRepository;

class OfferLogController
{
    /**
     * @var AuditLogRepository
     */
    private $auditLogRepository;
    
    /**
     * @var GridHelper
     */
    private $auditLogGridHelper;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var OrderBackendAuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuditLogRepository $auditLogRepository
     * @param GridHelper $auditLogGridHelper
     * @param OrderContextRepository $orderContextRepository
     * @param OfferService $offerService
     * @param CurrencyService $currencyService
     * @param OrderBackendAuthenticationService $authenticationService
     */
    public function __construct(
        AuditLogRepository $auditLogRepository,
        GridHelper $auditLogGridHelper,
        OrderContextRepository $orderContextRepository,
        OfferService $offerService,
        CurrencyService $currencyService,
        OrderBackendAuthenticationService $authenticationService
    ) {
        $this->auditLogRepository = $auditLogRepository;
        $this->auditLogGridHelper = $auditLogGridHelper;
        $this->orderContextRepository = $orderContextRepository;
        $this->offerService = $offerService;
        $this->currencyService = $currencyService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function logAction(Request $request): array
    {
        $orderContextId = (int) $request->requireParam('orderContextId');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $searchStruct = new AuditLogSearchStruct();

        $searchStruct->filters = [new NotEqualsFilter(
            $this->auditLogRepository::TABLE_ALIAS,
            'log_type',
            AuditLogValueOrderCommentEntity::class
        )];

        $this->auditLogGridHelper
            ->extractSearchDataInBackend($request, $searchStruct);

        $logItems = $this->auditLogRepository
            ->fetchList(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct, $currencyContext);

        $totalCount = $this->auditLogRepository
            ->fetchTotalCount(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct);

        return ['data' => $logItems, 'count' => $totalCount];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function commentListAction(Request $request): array
    {
        $orderContextId = (int) $request->requireParam('orderContextId');

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

        $logItems = $this->auditLogRepository
            ->fetchList(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct, $currencyContext);

        $totalCount = $this->auditLogRepository
            ->fetchTotalCount(OrderContextRepository::TABLE_NAME, $orderContextId, $searchStruct);

        return ['data' => $logItems, 'count' => $totalCount];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function commentAction(Request $request): array
    {
        $orderContextId = (int) $request->requireParam('orderContextId');
        $identity = $this->authenticationService->getIdentityByOrderContextId($orderContextId);

        $orderContext = $this->orderContextRepository->fetchOneOrderContextById($orderContextId, $identity->getOwnershipContext());

        $this->offerService
            ->saveComment((string) $request->getParam('comment'), $orderContext, $identity, true);

        return [];
    }
}
