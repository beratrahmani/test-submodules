<?php declare(strict_types = 1);

namespace Shopware\B2B\Offer\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\B2bControllerRedirectException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Offer\Framework\OfferCrudService;
use Shopware\B2B\Offer\Framework\OfferLineItemListRepository;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\Offer\Framework\OfferSearchStruct;
use Shopware\B2B\Offer\Framework\OfferService;
use Shopware\B2B\Shop\Framework\SessionStorageInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OfferController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @var OfferLineItemListRepository
     */
    private $offerLineItemListRepository;

    /**
     * @var OfferCrudService
     */
    private $offerCrudService;

    /**
     * @var SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @param AuthenticationService $authenticationService
     * @param CurrencyService $currencyService
     * @param GridHelper $gridHelper
     * @param OfferRepository $offerRepository
     * @param OfferService $offerService
     * @param OfferLineItemListRepository $offerLineItemListRepository
     * @param OfferCrudService $offerCrudService
     * @param SessionStorageInterface $sessionStorage
     */
    public function __construct(
        AuthenticationService $authenticationService,
        CurrencyService $currencyService,
        GridHelper $gridHelper,
        OfferRepository $offerRepository,
        OfferService $offerService,
        OfferLineItemListRepository $offerLineItemListRepository,
        OfferCrudService $offerCrudService,
        SessionStorageInterface $sessionStorage
    ) {
        $this->authenticationService = $authenticationService;
        $this->currencyService = $currencyService;
        $this->gridHelper = $gridHelper;
        $this->offerRepository = $offerRepository;
        $this->offerService = $offerService;
        $this->offerLineItemListRepository = $offerLineItemListRepository;
        $this->offerCrudService = $offerCrudService;
        $this->sessionStorage = $sessionStorage;
    }

    /**
     * @param Request $request
     * @return  array
     */
    public function indexAction(Request $request): array
    {
        return [];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $searchStruct = new OfferSearchStruct();
        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $this->gridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $offers = $this->offerRepository
            ->fetchList($ownershipContext, $searchStruct, $currencyContext);

        $totalCount = $this->offerRepository
            ->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper->getMaxPage($totalCount);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $offers, $maxPage, $currentPage);

        $sendToAdminMessage = $this->sessionStorage->get('showSendToAdminMessage');

        if ($sendToAdminMessage) {
            $this->sessionStorage->set('showSendToAdminMessage', null);
        }

        return [
            'gridState' => $gridState,
            'sendToAdminMessage' => $sendToAdminMessage,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        $offerId = (int) $request->requireParam('offerId');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        return ['offer' => $offer];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function editAction(Request $request): array
    {
        $offerId = (int) $request->requireParam('offerId');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $list = $this->offerLineItemListRepository->fetchOneListById($offer->listId, $currencyContext, $ownershipContext);

        return [
            'offer' => $offer,
            'list' => $list,
            'itemCount' => count($list->references),
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function sendOfferAction(Request $request)
    {
        $offerId = (int) $request->getParam('offerId');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $identity = $this->authenticationService
            ->getIdentity();

        $this->offerService->sendOfferToAdmin($offerId, $currencyContext, $identity);

        $this->sessionStorage->set('showSendToAdminMessage', true);

        throw new B2bControllerForwardException('edit');
    }

    /**
     * @param Request $request
     * @throws B2bControllerRedirectException
     * @return array
     */
    public function acceptAction(Request $request): array
    {
        $offerId = (int) $request
            ->requireParam('offerId');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $this->offerService
            ->convertOffer($offerId, $currencyContext, $ownershipContext);

        throw new B2bControllerRedirectException('confirm', 'checkout');
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     * @return array
     */
    public function declineOfferAction(Request $request): array
    {
        $offerId = (int) $request->requireParam('offerId');

        $currencyContext = $this->currencyService->createCurrencyContext();

        $identity = $this->authenticationService
            ->getIdentity();

        $this->offerService->declineOfferByUser($offerId, $currencyContext, $identity);

        throw new B2bControllerForwardException('edit');
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     * @return array
     */
    public function removeAction(Request $request): array
    {
        $data = $request->getPost();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $recordRequest = $this->offerCrudService->createExistingRecordRequest($data);

        $this->offerCrudService->remove($recordRequest, $currencyContext, $ownershipContext);

        throw new B2bControllerForwardException('grid');
    }

    /**
     * @param Request $request
     * @throws B2bControllerRedirectException
     */
    public function stopOfferAction(Request $request)
    {
        $this->offerService
            ->stopOffer();

        throw new B2bControllerRedirectException('index', 'b2boffer');
    }
}
