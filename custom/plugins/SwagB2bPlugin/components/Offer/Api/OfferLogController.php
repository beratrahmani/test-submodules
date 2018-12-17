<?php declare(strict_types = 1);

namespace Shopware\B2B\Offer\Api;

use Shopware\B2B\AuditLog\Framework\AuditLogRepository;
use Shopware\B2B\AuditLog\Framework\AuditLogSearchStruct;
use Shopware\B2B\AuditLog\Framework\AuditLogValueOrderCommentEntity;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\Filter\EqualsFilter;
use Shopware\B2B\Common\Filter\NotEqualsFilter;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\Offer\Framework\OfferService;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

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
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $contextIdentityLoader;

    /**
     * @param AuditLogRepository $auditLogRepository
     * @param GridHelper $auditLogGridHelper
     * @param OrderContextRepository $orderContextRepository
     * @param OfferService $offerService
     * @param CurrencyService $currencyService
     * @param OfferRepository $offerRepository
     * @param LoginContextService $loginContextService
     * @param DebtorAuthenticationIdentityLoader $contextIdentityLoader
     */
    public function __construct(
        AuditLogRepository $auditLogRepository,
        GridHelper $auditLogGridHelper,
        OrderContextRepository $orderContextRepository,
        OfferService $offerService,
        CurrencyService $currencyService,
        OfferRepository $offerRepository,
        LoginContextService $loginContextService,
        DebtorAuthenticationIdentityLoader $contextIdentityLoader
    ) {
        $this->auditLogRepository = $auditLogRepository;
        $this->auditLogGridHelper = $auditLogGridHelper;
        $this->orderContextRepository = $orderContextRepository;
        $this->offerService = $offerService;
        $this->currencyService = $currencyService;
        $this->offerRepository = $offerRepository;
        $this->loginContextService = $loginContextService;
        $this->contextIdentityLoader = $contextIdentityLoader;
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @param Request $request
     * @return array
     */
    public function logAction(string $debtorEmail, int $offerId, Request $request): array
    {
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $searchStruct = new AuditLogSearchStruct();

        $searchStruct->filters = [new NotEqualsFilter(
            $this->auditLogRepository::TABLE_ALIAS,
            'log_type',
            AuditLogValueOrderCommentEntity::class
        )];

        $this->auditLogGridHelper
            ->extractSearchDataInRestApi($request, $searchStruct);

        $logItems = $this->auditLogRepository
            ->fetchList(OrderContextRepository::TABLE_NAME, $offer->orderContextId, $searchStruct, $currencyContext);

        $totalCount = $this->auditLogRepository
            ->fetchTotalCount(OrderContextRepository::TABLE_NAME, $offer->orderContextId, $searchStruct);

        return ['success' => true, 'logs' => $logItems, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @param Request $request
     * @return array
     */
    public function commentListAction(string $debtorEmail, int $offerId, Request $request): array
    {
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $searchStruct = new AuditLogSearchStruct();

        $this->auditLogGridHelper->extractSearchDataInRestApi($request, $searchStruct);

        $searchStruct->filters = array_merge(
            $searchStruct->filters,
            [
                new EqualsFilter(
                    $this->auditLogRepository::TABLE_ALIAS,
                    'log_type',
                    AuditLogValueOrderCommentEntity::class
                ),
            ]
        );

        $logItems = $this->auditLogRepository
            ->fetchList(OrderContextRepository::TABLE_NAME, $offer->orderContextId, $searchStruct, $currencyContext);

        $totalCount = $this->auditLogRepository
            ->fetchTotalCount(OrderContextRepository::TABLE_NAME, $offer->orderContextId, $searchStruct);

        return ['success' => true, 'comments' => $logItems, 'totalCount' => $totalCount];
    }

    /**
     * @param Request $request
     * @param string $debtorEmail
     * @param int $offerId
     * @return array
     */
    public function commentAction(string $debtorEmail, int $offerId, Request $request): array
    {
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $identity = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService);

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        $orderContext = $this->orderContextRepository->fetchOneOrderContextById($offer->orderContextId, $identity->getOwnershipContext());

        $this->offerService
            ->saveComment((string) $request->getParam('comment'), $orderContext, $identity, true);

        return $this->commentListAction($debtorEmail, $offerId, $request);
    }
}
