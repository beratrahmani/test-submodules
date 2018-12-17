<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Api;

use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\Offer\Framework\OfferCrudService;
use Shopware\B2B\Offer\Framework\OfferDiscountService;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\Offer\Framework\OfferSearchStruct;
use Shopware\B2B\Offer\Framework\OfferService;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

class OfferController
{
    /**
     * @var GridHelper
     */
    private $offerGridHelper;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $contextIdentityLoader;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var OfferCrudService
     */
    private $offerCrudService;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @var OfferDiscountService
     */
    private $offerDiscountService;

    /**
     * @param OfferRepository $offerRepository
     * @param OfferCrudService $offerCrudService
     * @param GridHelper $offerGridHelper
     * @param DebtorAuthenticationIdentityLoader $contextIdentityLoader
     * @param LoginContextService $loginContextService
     * @param CurrencyService $currencyService
     * @param OfferService $offerService
     * @param OfferDiscountService $offerDiscountService
     */
    public function __construct(
        OfferRepository $offerRepository,
        OfferCrudService $offerCrudService,
        GridHelper $offerGridHelper,
        DebtorAuthenticationIdentityLoader $contextIdentityLoader,
        LoginContextService $loginContextService,
        CurrencyService $currencyService,
        OfferService $offerService,
        OfferDiscountService $offerDiscountService
    ) {
        $this->offerGridHelper = $offerGridHelper;
        $this->contextIdentityLoader = $contextIdentityLoader;
        $this->loginContextService = $loginContextService;
        $this->offerRepository = $offerRepository;
        $this->currencyService = $currencyService;
        $this->offerCrudService = $offerCrudService;
        $this->offerService = $offerService;
        $this->offerDiscountService = $offerDiscountService;
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function getListAction(string $debtorEmail, Request $request): array
    {
        $search = new OfferSearchStruct();
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $currencyContext = $this->currencyService->createCurrencyContext();

        $this->offerGridHelper
            ->extractSearchDataInRestApi($request, $search);

        $offerEntities = $this->offerRepository
            ->fetchList($ownershipContext, $search, $currencyContext);

        $totalCount = $this->offerRepository
            ->fetchTotalCount($ownershipContext, $search);

        return ['success' => true, 'offers' => $offerEntities, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @return array
     */
    public function getAction(string $debtorEmail, int $offerId): array
    {
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $currencyContext = $this->currencyService->createCurrencyContext();

        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        return ['success' => true, 'offer' => $offer];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @param int $offerId
     * @return array
     */
    public function removeAction(string $debtorEmail, int $offerId, Request $request): array
    {
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $currencyContext = $this->currencyService->createCurrencyContext();

        $post = $request->getPost();
        $post['id'] = $offerId;

        $request = $this->offerCrudService->createExistingRecordRequest($post);
        $offer = $this->offerCrudService->remove($request, $currencyContext, $ownershipContext, true);

        return ['success' => true, 'offer' => $offer];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @param int $offerId
     * @return array
     */
    public function updateAction(string $debtorEmail, int $offerId, Request $request): array
    {
        $post = $request->getPost();
        $post['id'] = $offerId;

        $debtorIdentity = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService);

        $currencyContext = $this->currencyService->createCurrencyContext();

        $serviceRequest = $this->offerCrudService
            ->createExistingRecordRequest($post);

        $offer = $this->offerCrudService
            ->update($serviceRequest, $currencyContext, $debtorIdentity, true);

        return ['success' => true, 'offer' => $offer];
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @param Request $request
     * @return array
     */
    public function updateOfferExpiredDateAction(string $debtorEmail, int $offerId, Request $request): array
    {
        $context = $this->currencyService->createCurrencyContext();

        $debtorIdentity = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService);

        $offer = $this->offerRepository->fetchOfferById($offerId, $context, $debtorIdentity->getOwnershipContext());

        $expiredDate = $request->getParam('expiredAt');

        $offer = $this->offerService->updateExpiredDate($expiredDate, $offer, $debtorIdentity);

        return ['success' => true, 'offer' => $offer];
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @return array
     */
    public function acceptAction(string $debtorEmail, int $offerId): array
    {
        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $identity = $this->contextIdentityLoader->fetchIdentityByEmail($debtorEmail, $this->loginContextService);

        $this->offerService
            ->acceptOffer($offerId, $currencyContext, $identity);

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        return ['success' => true, 'offer' => $offer];
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @return array
     */
    public function declineOfferAction(string $debtorEmail, int $offerId): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();

        $identity = $this->contextIdentityLoader->fetchIdentityByEmail($debtorEmail, $this->loginContextService);

        $this->offerService->declineOffer($offerId, $currencyContext, $identity);

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        return ['success' => true, 'offer' => $offer];
    }
}
