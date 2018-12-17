<?php declare(strict_types = 1);

namespace Shopware\B2B\Offer\Backend;

use Shopware\B2B\Address\Framework\AddressRepositoryInterface;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\LineItemList\Framework\ProductProviderInterface;
use Shopware\B2B\Offer\Framework\OfferBackendAuthenticationService;
use Shopware\B2B\Offer\Framework\OfferCrudService;
use Shopware\B2B\Offer\Framework\OfferDiscountService;
use Shopware\B2B\Offer\Framework\OfferEntity;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceEntity;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\Offer\Framework\OfferSearchStruct;
use Shopware\B2B\Offer\Framework\OfferService;

class OfferController
{
    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var OfferCrudService
     */
    private $offerCrudService;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @var LineItemListRepository
     */
    private $listRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var ProductProviderInterface
     */
    private $productProvider;

    /**
     * @var OfferDiscountService
     */
    private $offerDiscountService;

    /**
     * @var OfferBackendAuthenticationService
     */
    private $authenticationService;

    /**
     * @param CurrencyService $currencyService
     * @param OfferRepository $offerRepository
     * @param GridHelper $gridHelper
     * @param OfferCrudService $offerCrudService
     * @param OfferService $offerService
     * @param LineItemListRepository $listRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param ProductProviderInterface $productProvider
     * @param OfferDiscountService $offerDiscountService
     * @param OfferBackendAuthenticationService $authenticationService
     */
    public function __construct(
        CurrencyService $currencyService,
        OfferRepository $offerRepository,
        GridHelper $gridHelper,
        OfferCrudService $offerCrudService,
        OfferService $offerService,
        LineItemListRepository $listRepository,
        AddressRepositoryInterface $addressRepository,
        ProductProviderInterface $productProvider,
        OfferDiscountService $offerDiscountService,
        OfferBackendAuthenticationService $authenticationService
    ) {
        $this->currencyService = $currencyService;
        $this->offerRepository = $offerRepository;
        $this->gridHelper = $gridHelper;
        $this->offerCrudService = $offerCrudService;
        $this->offerService = $offerService;
        $this->listRepository = $listRepository;
        $this->addressRepository = $addressRepository;
        $this->productProvider = $productProvider;
        $this->offerDiscountService = $offerDiscountService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function listAction(Request $request): array
    {
        return $this->modelAction($request);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function modelAction(Request $request): array
    {
        $context = $this->currencyService->createCurrencyContext();

        $searchStruct = new OfferSearchStruct();
        $filter = $request->getParam('filter');

        if ($filter[0] && $filter[0]['property'] === 'status') {
            $searchStruct->searchStatus = $filter[0]['value'];
        }

        $this->gridHelper->extractSearchDataInBackend($request, $searchStruct);

        $offers = [];
        $offers = array_map(
            function (OfferEntity $offer) use ($offers, $context) {
                return $offers[] = $this->enrichOffer($offer, $context);
            },
            $this->offerRepository->fetchBackendList($searchStruct, $context)
        );

        $count = $this->offerRepository->fetchTotalCountForBackend($searchStruct);

        return ['data' => $offers, 'count' => $count];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        $offerId = (int) $request->getParam('id');

        $context = $this->currencyService->createCurrencyContext();
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $offer = $this->offerRepository->fetchOfferById($offerId, $context, $identity->getOwnershipContext());

        return ['data' => $this->enrichOffer($offer, $context)];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function deleteAction(Request $request): array
    {
        $recordRequest = $this->offerCrudService->createExistingRecordRequest($request->getPost());

        $context = $this->currencyService->createCurrencyContext();
        $offerId = (int) $recordRequest->requireParam('id');
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $offer = $this->offerCrudService->remove($recordRequest, $context, $identity->getOwnershipContext());

        return ['data' => $offer];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function updateOfferExpiredDateAction(Request $request): array
    {
        $offerId = (int) $request->getParam('id');

        $context = $this->currencyService->createCurrencyContext();
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $offer = $this->offerRepository->fetchOfferById($offerId, $context, $identity->getOwnershipContext());

        $expiredDate = $request->getParam('expiredAt');

        $offer = $this->offerService->updateExpiredDate($expiredDate, $offer, $identity);

        return ['data' => $offer];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function updateDiscountAction(Request $request): array
    {
        $offerId = (int) $request->getParam('id');
        $discount = (float) $request->getParam('discount');
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $currencyContext = $this->currencyService->createCurrencyContext();

        $offer = $this->offerDiscountService->updateDiscount($offerId, $currencyContext, $discount, $identity, true);

        return ['data' => $offer];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function declineOfferAction(Request $request): array
    {
        $offerId = (int) $request->getParam('id');
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $offer = $this->offerService->declineOffer($offerId, $this->currencyService->createCurrencyContext(), $identity);

        return ['data' => $offer];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function acceptOfferAction(Request $request): array
    {
        $offerId = (int) $request->getParam('id');
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $offer = $this->offerService->acceptOffer(
            $offerId,
            $this->currencyService->createCurrencyContext(),
            $identity
        );

        return ['data' => $offer];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function fetchDebtorIdByOfferIdAction(Request $request): array
    {
        $request->checkPost();

        $offerId = (int) $request->requireParam('id');
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $debtor = $this->offerService->fetchDebtorByOfferId(
            $offerId,
            $this->currencyService->createCurrencyContext(),
            $identity->getOwnershipContext()
        );

        return ['debtorId' => $debtor->id];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function calculatePriceAction(Request $request): array
    {
        $request->checkPost();

        $offer = new OfferEntity();
        $offer->setData($request->getPost());
        $offer->id = (int) $offer->id;

        $identity = $this->authenticationService->getIdentityByOfferId($offer->id);

        $lineItemList = new LineItemList();
        $lineItemList->id = (int) $offer->listId;

        foreach (json_decode($request->getParam('lineItems')) as $item) {
            $reference = new OfferLineItemReferenceEntity();
            $reference->setData(get_object_vars($item));
            $lineItemList->references[] = $reference;
        }

        $this->offerService->updateOffer($offer, $lineItemList, $this->currencyService->createCurrencyContext(), $identity->getOwnershipContext());

        $offer->discountAmountNet = round($offer->discountAmountNet, 2);

        $this->productProvider->updateList($lineItemList);

        return ['data' => array_merge(
            $offer->toArray(),
            [
                'listAmount' => $lineItemList->amount,
                'listAmountNet' => round($lineItemList->amountNet, 2),
            ]
        )];
    }

    /**
     * @internal
     * @param OfferEntity $offer
     * @param CurrencyContext $context
     * @return array
     */
    protected function enrichOffer(OfferEntity $offer, CurrencyContext $context): array
    {
        $offerArray = $offer->toArray();
        $identity = $this->authenticationService->getIdentityByOfferId($offer->id);

        $list = $this->listRepository->fetchOneListById($offer->listId, $context, $identity->getOwnershipContext());

        $offerArray['listAmount'] = $list->amount;
        $offerArray['listAmountNet'] = $list->amountNet;
        $offerArray['listPositionCount'] = count($list->references);

        try {
            $debtor = $this->offerService->fetchDebtorByOfferId(
                $offer->id,
                $this->currencyService->createCurrencyContext(),
                $identity->getOwnershipContext()
            );

            $address = $this->addressRepository->fetchOneById($debtor->default_billing_address_id, $identity);
            $offerArray['debtorCompany'] = $address->company;
        } catch (NotFoundException $e) {
            $offerArray['debtorCompany'] = '';
        }

        return $offerArray;
    }
}
