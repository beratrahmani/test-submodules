<?php declare(strict_types = 1);

namespace Shopware\B2B\Offer\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\LineItemList\Framework\LineItemReference;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceSearchStruct;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceService;
use Shopware\B2B\Offer\Framework\DiscountGreaterThanAmountException;
use Shopware\B2B\Offer\Framework\OfferDiscountService;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceCrudService;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceRepository;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceService;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OfferLineItemReferenceController
{
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferLineItemReferenceRepository
     */
    private $lineItemReferenceRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var LineItemReferenceService
     */
    private $lineItemReferenceService;

    /**
     * @var OfferLineItemReferenceCrudService
     */
    private $offerLineItemReferenceCrudService;

    /**
     * @var OfferDiscountService
     */
    private $offerDiscountService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param OfferRepository $offerRepository
     * @param OfferLineItemReferenceRepository $lineItemReferenceRepository
     * @param OfferLineItemReferenceService $lineItemReferenceService
     * @param LineItemListRepository $lineItemListRepository
     * @param CurrencyService $currencyService
     * @param GridHelper $gridHelper
     * @param OfferLineItemReferenceCrudService $offerLineItemReferenceCrudService
     * @param OfferDiscountService $offerDiscountService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        OfferRepository $offerRepository,
        OfferLineItemReferenceRepository $lineItemReferenceRepository,
        OfferLineItemReferenceService $lineItemReferenceService,
        LineItemListRepository $lineItemListRepository,
        CurrencyService $currencyService,
        GridHelper $gridHelper,
        OfferLineItemReferenceCrudService $offerLineItemReferenceCrudService,
        OfferDiscountService $offerDiscountService,
        AuthenticationService $authenticationService
    ) {
        $this->offerRepository = $offerRepository;
        $this->lineItemReferenceRepository = $lineItemReferenceRepository;
        $this->currencyService = $currencyService;
        $this->lineItemListRepository = $lineItemListRepository;
        $this->gridHelper = $gridHelper;
        $this->lineItemReferenceService = $lineItemReferenceService;
        $this->offerLineItemReferenceCrudService = $offerLineItemReferenceCrudService;
        $this->offerDiscountService = $offerDiscountService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function gridAction(Request $request)
    {
        $offerId = (int) $request->requireParam('offerId');

        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $lineItemList = $this->lineItemListRepository
            ->fetchOneListById($offer->listId, $currencyContext, $ownershipContext);

        $searchStruct = new LineItemReferenceSearchStruct();

        $this->gridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        $items = $this->lineItemReferenceService
            ->fetchLineItemsReferencesWithProductNames($offer->listId, $searchStruct, $ownershipContext);

        $totalCount = $this->lineItemReferenceRepository
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
            $this->getHeaderData($lineItemList, $totalCount),
            $validationResponse
        );
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function updateDiscountAction(Request $request)
    {
        $identity = $this->authenticationService->getIdentity();

        $request->checkPost();

        $offerId = (int) $request->requireParam('offerId');
        $discount = (float) $request->getParam('discount');

        $currencyContext = $this->currencyService->createCurrencyContext();

        try {
            $this->offerDiscountService->updateDiscount($offerId, $currencyContext, $discount, $identity, false);
        } catch (ValidationException $e) {
            $this->gridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('grid', null, null, ['id' => $offerId]);
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
            throw new B2bControllerForwardException('new', null, null, ['offerId' => $request->requireParam('offerId')]);
        }

        throw new B2bControllerForwardException('grid', null, null, ['offerId' => $request->requireParam('offerId')]);
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->authenticationService->getIdentity();

        $offerId = (int) $request->requireParam('offerId');
        $listId = (int) $request->requireParam('listId');

        $post = $request->getPost();

        $post['discountAmountNet'] = (float) $request->getParam('discountAmountNet');

        $crudRequest = $this->offerLineItemReferenceCrudService
            ->createUpdateCrudRequest($post);

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

        $offerId = (int) $request->getParam('offerId');
        $lineItemId = (int) $request->getParam('lineItemId');

        $identity = $this->authenticationService->getIdentity();

        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

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
     * @internal
     * @param Request $request
     * @return LineItemReference
     */
    protected function createLineItemReferenceFromRequest(Request $request): LineItemReference
    {
        $identity = $this->authenticationService->getIdentity();

        $request->checkPost();
        $currencyContext = $this->currencyService->createCurrencyContext();

        $offerId = (int) $request->requireParam('offerId');

        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        $crudRequest = $this->offerLineItemReferenceCrudService
            ->createCreateCrudRequest($request->getPost());

        return $this->offerLineItemReferenceCrudService
            ->addLineItem($offer->listId, $offerId, $crudRequest, $currencyContext, $identity);
    }

    /**
     * @internal
     * @param LineItemList $list
     * @param int $totalCount
     * @return array
     */
    protected function getHeaderData(LineItemList $list, int $totalCount): array
    {
        return [
            'itemCount' => $totalCount,
            'amountNet' => $list->amountNet,
            'amount' => $list->amount,
        ];
    }
}
