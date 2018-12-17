<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Frontend;

use Shopware\B2B\Cart\Bridge\CartState;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\B2bControllerRedirectException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceSearchStruct;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceService;
use Shopware\B2B\LineItemList\Framework\LineItemShopWriterServiceInterface;
use Shopware\B2B\Offer\Framework\DiscountGreaterThanAmountException;
use Shopware\B2B\Offer\Framework\OfferDiscountService;
use Shopware\B2B\Offer\Framework\OfferEntity;
use Shopware\B2B\Offer\Framework\OfferLineItemListRepository;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceCrudService;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceRepository;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceService;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\Offer\Framework\OfferService;
use Shopware\B2B\Offer\Framework\UnexpectedOfferStateException;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\Order\Framework\OrderContextService;
use Shopware\B2B\Shop\Framework\SessionStorageInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OfferThroughCheckoutController
{
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferLineItemReferenceRepository
     */
    private $offerLineItemReferenceRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var LineItemListRepository
     */
    private $offerLineItemListRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var LineItemReferenceService
     */
    private $offerLineItemReferenceService;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @var OfferLineItemReferenceCrudService
     */
    private $offerLineItemReferenceCrudService;

    /**
     * @var SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OfferDiscountService
     */
    private $offerDiscountService;

    /**
     * @var LineItemShopWriterServiceInterface
     */
    private $lineItemShopWriterService;

    /**
     * @var OrderContextService
     */
    private $orderContextService;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var CartState
     */
    private $cartState;

    /**
     * @param OfferRepository $offerRepository
     * @param OfferLineItemReferenceRepository $offerLineItemReferenceRepository
     * @param OfferLineItemReferenceService $offerLineItemReferenceService
     * @param OfferLineItemListRepository $offerLineItemListRepository
     * @param CurrencyService $currencyService
     * @param GridHelper $gridHelper
     * @param OfferService $offerService
     * @param OfferLineItemReferenceCrudService $offerLineItemReferenceCrudService
     * @param SessionStorageInterface $sessionStorage
     * @param OfferDiscountService $offerDiscountService
     * @param LineItemShopWriterServiceInterface $lineItemShopWriterService
     * @param AuthenticationService $authenticationService
     * @param OrderContextService $orderContextService
     * @param OrderContextRepository $orderContextRepository
     * @param CartState $cartState
     */
    public function __construct(
        OfferRepository $offerRepository,
        OfferLineItemReferenceRepository $offerLineItemReferenceRepository,
        OfferLineItemReferenceService $offerLineItemReferenceService,
        OfferLineItemListRepository $offerLineItemListRepository,
        CurrencyService $currencyService,
        GridHelper $gridHelper,
        OfferService $offerService,
        OfferLineItemReferenceCrudService $offerLineItemReferenceCrudService,
        SessionStorageInterface $sessionStorage,
        OfferDiscountService $offerDiscountService,
        LineItemShopWriterServiceInterface $lineItemShopWriterService,
        AuthenticationService $authenticationService,
        OrderContextService $orderContextService,
        OrderContextRepository $orderContextRepository,
        CartState $cartState
    ) {
        $this->offerRepository = $offerRepository;
        $this->offerLineItemReferenceRepository = $offerLineItemReferenceRepository;
        $this->currencyService = $currencyService;
        $this->offerLineItemListRepository = $offerLineItemListRepository;
        $this->gridHelper = $gridHelper;
        $this->offerLineItemReferenceService = $offerLineItemReferenceService;
        $this->offerService = $offerService;
        $this->offerLineItemReferenceCrudService = $offerLineItemReferenceCrudService;
        $this->sessionStorage = $sessionStorage;
        $this->offerDiscountService = $offerDiscountService;
        $this->lineItemShopWriterService = $lineItemShopWriterService;
        $this->authenticationService = $authenticationService;
        $this->orderContextService = $orderContextService;
        $this->orderContextRepository = $orderContextRepository;
        $this->cartState = $cartState;
    }

    /**
     * @param Request $request
     * @throws UnexpectedOfferStateException
     * @throws B2bControllerRedirectException
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $offerId = (int) $request->getParam('offerId');

        $currencyContext = $this->currencyService->createCurrencyContext();

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $ownershipContext);
        } catch (NotFoundException $e) {
            throw new B2bControllerRedirectException('confirm', 'checkout');
        }

        if ($offer->status !== OfferEntity::STATE_OPEN) {
            throw new UnexpectedOfferStateException('Unsupported Mode');
        }

        return ['offerId' => $offerId];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request): array
    {
        $offerId = (int) $request->requireParam('offerId');

        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $lineItemList = $this->offerLineItemListRepository
            ->fetchOneListById($offer->listId, $currencyContext, $ownershipContext);

        $searchStruct = new LineItemReferenceSearchStruct();

        $this->gridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $searchStruct->offset = 0;
        $searchStruct->limit = PHP_INT_MAX;

        $items = $this->offerLineItemReferenceService
            ->fetchLineItemsReferencesWithProductNames($offer->listId, $searchStruct, $ownershipContext);

        $totalCount = $this->offerLineItemReferenceRepository
            ->fetchTotalCount($offer->listId, $searchStruct, $ownershipContext);

        $currentPage = $this->gridHelper
            ->getCurrentPage($request);

        $maxPage = $this->gridHelper
            ->getMaxPage($totalCount);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $items, $currentPage, $maxPage);

        $validationResponse = $this->gridHelper
            ->getValidationResponse('lineItemReference');

        return array_merge(
            [
                'gridState' => $gridState,
                'offer' => $offer,
                'discountMessage' => (bool) $request->getParam('discountMessage'),
            ],
            $this->getHeaderData($lineItemList),
            $validationResponse
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $offerId = (int) $request->requireParam('offerId');
        $listId = (int) $request->requireParam('listId');

        $post = $request->getPost();
        $post['discountAmountNet'] = (float) $request->getParam('discountAmountNet');

        $crudRequest = $this->offerLineItemReferenceCrudService
            ->createUpdateCrudRequest($post);

        $identity = $this->authenticationService->getIdentity();

        try {
            $this->offerLineItemReferenceCrudService
                ->updateLineItem($listId, $offerId, $crudRequest, $currencyContext, $identity);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
        }

        $discountMessage = false;
        try {
            $this->offerDiscountService->checkOfferDiscountGreaterThanAmount($offerId, $currencyContext, $identity);
        } catch (DiscountGreaterThanAmountException $e) {
            $discountMessage = true;
        }

        throw new B2bControllerForwardException(
            'grid',
            null,
            null,
            [
                'offerId' => $offerId,
                'discountMessage' => $discountMessage,
            ]
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();

        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->authenticationService->getIdentity();

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $offerId = (int) $request->getParam('offerId');
        $lineItemId = (int) $request->getParam('lineItemId');

        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $this->offerLineItemReferenceCrudService
            ->deleteLineItem($offerId, $offer->listId, $lineItemId, $currencyContext, $identity);

        $discountMessage = false;
        try {
            $this->offerDiscountService->checkOfferDiscountGreaterThanAmount($offerId, $currencyContext, $identity);
        } catch (DiscountGreaterThanAmountException $e) {
            $discountMessage = true;
        }

        throw new B2bControllerForwardException(
            'grid',
            null,
            null,
            [
                'id' => $offerId,
                'discountMessage' => $discountMessage,
            ]
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newAction(Request $request): array
    {
        $offerId = (int) $request->requireParam('offerId');

        $currencyContext = $this->currencyService->createCurrencyContext();

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $validationResponse = $this->gridHelper
            ->getValidationResponse('lineItemReference');

        return array_merge(
            ['offer' => $offer],
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
        }

        throw new B2bControllerForwardException('new');
    }

    /**
     * @internal
     * @param Request $request
     * @return LineItemReference
     */
    protected function createLineItemReferenceFromRequest(Request $request): LineItemReference
    {
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->authenticationService->getIdentity();
        $offerId = (int) $request->requireParam('offerId');

        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $crudRequest = $this->offerLineItemReferenceCrudService
            ->createCreateCrudRequest($request->getPost());

        $reference = $this->offerLineItemReferenceCrudService
            ->addLineItem($offer->listId, $offerId, $crudRequest, $currencyContext, $identity);

        return $reference;
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function updateDiscountAction(Request $request)
    {
        $request->checkPost();

        $offerId = (int) $request->requireParam('offerId');
        $discount = (float) $request->getParam('discount');

        $currencyContext = $this->currencyService->createCurrencyContext();

        $identity = $this->authenticationService->getIdentity();

        try {
            $this->offerDiscountService->updateDiscount($offerId, $currencyContext, $discount, $identity, false);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('grid', null, null, ['id' => $offerId]);
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

    /**
     * @param Request $request
     * @throws B2bControllerRedirectException
     */
    public function sendOfferAction(Request $request)
    {
        $offerId = (int) $request->getParam('offerId');

        $comment = $request->getParam('comment');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $identity = $this->authenticationService->getIdentity();

        $this->offerService->sendOfferToAdmin($offerId, $currencyContext, $identity);

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        $orderContext = $this->orderContextRepository
            ->fetchOneOrderContextById($offer->orderContextId, $identity->getOwnershipContext());

        $this->orderContextService
            ->saveComment((string) $comment, $orderContext);

        $this->sessionStorage->set('showSendToAdminMessage', true);

        throw new B2bControllerRedirectException('index', 'b2boffer');
    }

    /**
     * @param Request $request
     * @throws B2bControllerRedirectException
     */
    public function backToCheckoutAction(Request $request)
    {
        $offerId = (int) $request->getParam('offerId');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $ownershipContext);

            $list = $this->offerLineItemListRepository->fetchOneListById($offer->listId, $currencyContext, $ownershipContext);

            $this->lineItemShopWriterService->triggerCart($list, false);

            if (!$this->cartState->hasOldState() || $this->cartState->getOldState() === 'order') {
                $this->offerRepository->removeOffer($offer, $ownershipContext);
            } else {
                $this->offerRepository->removeOfferWithoutContext($offer);
            }

            $this->cartState->resetState();
        } catch (NotFoundException $notFoundException) {
            // nth
        }

        throw new B2bControllerRedirectException('confirm', 'checkout');
    }
}
