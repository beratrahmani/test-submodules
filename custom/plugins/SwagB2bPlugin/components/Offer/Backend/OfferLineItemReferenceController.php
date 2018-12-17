<?php declare(strict_types = 1);

namespace Shopware\B2B\Offer\Backend;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceSearchStruct;
use Shopware\B2B\LineItemList\Framework\ProductProviderInterface;
use Shopware\B2B\Offer\Framework\DiscountGreaterThanAmountException;
use Shopware\B2B\Offer\Framework\OfferBackendAuthenticationService;
use Shopware\B2B\Offer\Framework\OfferDiscountService;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceCrudService;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceEntity;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceRepository;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceService;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceValidationService;
use Shopware\B2B\Offer\Framework\OfferRepository;

class OfferLineItemReferenceController
{
    /**
     * @var OfferLineItemReferenceCrudService
     */
    private $offerLineItemReferenceCrud;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferLineItemReferenceRepository
     */
    private $offerLineItemReferenceRepository;

    /**
     * @var OfferDiscountService
     */
    private $offerDiscountService;

    /**
     * @var ProductProviderInterface
     */
    private $productProvider;

    /**
     * @var OfferLineItemReferenceService
     */
    private $offerLineItemReferenceService;

    /**
     * @var OfferBackendAuthenticationService
     */
    private $authenticationService;

    /**
     * @var OfferLineItemReferenceValidationService
     */
    private $lineItemReferenceValidationService;

    /**
     * @param OfferLineItemReferenceCrudService $offerLineItemReferenceCrud
     * @param CurrencyService $currencyService
     * @param OfferRepository $offerRepository
     * @param OfferLineItemReferenceRepository $offerLineItemReferenceRepository
     * @param OfferDiscountService $offerDiscountService
     * @param ProductProviderInterface $productProvider
     * @param OfferLineItemReferenceService $offerLineItemReferenceService
     * @param OfferBackendAuthenticationService $authenticationService
     * @param OfferLineItemReferenceValidationService $lineItemReferenceValidationService
     */
    public function __construct(
        OfferLineItemReferenceCrudService $offerLineItemReferenceCrud,
        CurrencyService $currencyService,
        OfferRepository $offerRepository,
        OfferLineItemReferenceRepository $offerLineItemReferenceRepository,
        OfferDiscountService $offerDiscountService,
        ProductProviderInterface $productProvider,
        OfferLineItemReferenceService $offerLineItemReferenceService,
        OfferBackendAuthenticationService $authenticationService,
        OfferLineItemReferenceValidationService $lineItemReferenceValidationService
    ) {
        $this->offerLineItemReferenceCrud = $offerLineItemReferenceCrud;
        $this->currencyService = $currencyService;
        $this->offerRepository = $offerRepository;
        $this->offerLineItemReferenceRepository = $offerLineItemReferenceRepository;
        $this->offerDiscountService = $offerDiscountService;
        $this->productProvider = $productProvider;
        $this->offerLineItemReferenceService = $offerLineItemReferenceService;
        $this->authenticationService = $authenticationService;
        $this->lineItemReferenceValidationService = $lineItemReferenceValidationService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function updateReferenceAction(Request $request): array
    {
        $updateRequest = $this->offerLineItemReferenceCrud->createUpdateCrudRequest($request->getPost());

        $offerId = (int) $request->requireParam('offer_id');
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $context = $this->currencyService->createCurrencyContext();

        $offer = $this->offerRepository->fetchOfferById($offerId, $context, $identity->getOwnershipContext());

        $reference =
            $this->offerLineItemReferenceCrud->updateLineItem(
                $offer->listId,
                $offerId,
                $updateRequest,
                $context,
                $identity,
                true
            );

        $discountMessage = false;
        try {
            $this->offerDiscountService->checkOfferDiscountGreaterThanAmount($offerId, $context, $identity, true);
        } catch (DiscountGreaterThanAmountException $e) {
            $discountMessage = true;
        }

        return ['data' => $reference, 'discountMessage' => $discountMessage];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function deleteReferenceAction(Request $request): array
    {
        $referenceId = (int) $request->requireParam('id');

        $context = $this->currencyService->createCurrencyContext();

        $offerId = (int) $request->requireParam('offer_id');
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $offer = $this->offerRepository->fetchOfferById($offerId, $context, $identity->getOwnershipContext());

        $data = $this->offerLineItemReferenceCrud->deleteLineItem($offerId, $offer->listId, $referenceId, $context, $identity, true);

        $discountMessage = false;
        try {
            $this->offerDiscountService->checkOfferDiscountGreaterThanAmount($offerId, $context, $identity, true);
        } catch (DiscountGreaterThanAmountException $e) {
            $discountMessage = true;
        }

        return ['data' => $data, 'discountMessage' => $discountMessage];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function createLineItemAction(Request $request): array
    {
        $request->checkPost();

        $data = $request->getPost();
        $data['quantity'] = (int) $data['quantity'];
        $offerId = (int) $request->requireParam('offer_id');

        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $context = $this->currencyService->createCurrencyContext();

        $offer = $this->offerRepository->fetchOfferById($offerId, $context, $identity->getOwnershipContext());

        $createRequest = $this->offerLineItemReferenceCrud->createCreateCrudRequest($data);

        $data = $this->offerLineItemReferenceCrud
            ->addLineItem($offer->listId, $offer->id, $createRequest, $context, $identity, true);

        return ['data' => $data];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getAllPositionsAction(Request $request): array
    {
        $offerId = (int) $request->requireParam('offer_id');

        $context = $this->currencyService->createCurrencyContext();
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $offer = $this->offerRepository->fetchOfferById($offerId, $context, $identity->getOwnershipContext());

        $searchStruct = new LineItemReferenceSearchStruct();

        $searchStruct->limit = PHP_INT_MAX;

        $references = $this->offerLineItemReferenceService->fetchLineItemsReferencesWithProductNames($offer->listId, $searchStruct, $identity->getOwnershipContext());

        $count = $this->offerLineItemReferenceRepository->fetchTotalCount($offer->listId, $searchStruct, $identity->getOwnershipContext());

        return ['data' => $references, 'count' => $count];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function updateDiscountAction(Request $request): array
    {
        $request->checkPost();

        $offerId = (int) $request->requireParam('offer_id');
        $discount = (float) $request->requireParam('discount');
        $identity = $this->authenticationService->getIdentityByOfferId($offerId);

        $currencyContext = $this->currencyService->createCurrencyContext();

        $this->offerDiscountService->updateDiscount($offerId, $currencyContext, $discount, $identity, true);

        return [];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getProductPriceForItemAndQuantityAction(Request $request): array
    {
        $request->checkPost();

        $referenceNumber = $request->requireParam('referenceNumber');
        $quantity = (int) $request->requireParam('quantity');

        $lineItemReference = new OfferLineItemReferenceEntity();
        $lineItemReference->quantity = $quantity;
        $lineItemReference->referenceNumber = $referenceNumber;

        $this->productProvider->updateReference($lineItemReference);
        $this->productProvider->setMaxMinAndSteps($lineItemReference);

        if ($quantity < $lineItemReference->minPurchase) {
            $lineItemReference->quantity = $lineItemReference->minPurchase;
        }

        $lineItemReference->amount = round($lineItemReference->amount, 2);
        $lineItemReference->amountNet = round($lineItemReference->amountNet, 2);

        return ['data' => $lineItemReference];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateEntityAction(Request $request): array
    {
        $request->checkPost();

        $referenceNumber = $request->requireParam('referenceNumber');
        $quantity = (int) $request->requireParam('quantity');

        $lineItemReference = new OfferLineItemReferenceEntity();
        $lineItemReference->quantity = $quantity;
        $lineItemReference->referenceNumber = $referenceNumber;

        $this->productProvider->updateReference($lineItemReference);
        $this->productProvider->setMaxMinAndSteps($lineItemReference);

        $valitdator = $this->lineItemReferenceValidationService->createCrudValidator($lineItemReference);

        if (count($valitdator->getViolations())) {
            $errors = ['valid' => false];

            foreach ($valitdator->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return $errors;
        }

        return ['valid' => true];
    }
}
