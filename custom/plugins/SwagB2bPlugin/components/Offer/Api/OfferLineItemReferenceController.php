<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Api;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\Offer\Framework\OfferLineItemListRepository;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceCrudService;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

class OfferLineItemReferenceController
{
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
     * @var OfferLineItemReferenceCrudService
     */
    private $offerLineItemReferenceCrudService;

    /**
     * @var OfferLineItemListRepository
     */
    private $offerLineItemListRepository;

    /**
     * @param OfferRepository $offerRepository
     * @param DebtorAuthenticationIdentityLoader $contextIdentityLoader
     * @param LoginContextService $loginContextService
     * @param CurrencyService $currencyService
     * @param OfferLineItemReferenceCrudService $offerLineItemReferenceCrudService
     * @param OfferLineItemListRepository $offerLineItemListRepository
     */
    public function __construct(
        OfferRepository $offerRepository,
        DebtorAuthenticationIdentityLoader $contextIdentityLoader,
        LoginContextService $loginContextService,
        CurrencyService $currencyService,
        OfferLineItemReferenceCrudService $offerLineItemReferenceCrudService,
        OfferLineItemListRepository $offerLineItemListRepository
    ) {
        $this->contextIdentityLoader = $contextIdentityLoader;
        $this->loginContextService = $loginContextService;
        $this->offerRepository = $offerRepository;
        $this->currencyService = $currencyService;
        $this->offerLineItemReferenceCrudService = $offerLineItemReferenceCrudService;
        $this->offerLineItemListRepository = $offerLineItemListRepository;
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @param Request $request
     * @return array
     */
    public function addItemsAction(string $debtorEmail, int $offerId, Request $request): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->contextIdentityLoader->fetchIdentityByEmail($debtorEmail, $this->loginContextService);

        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        $items = $request->getPost();

        $references = [];
        foreach ($items as $item) {
            $crudService = $this->offerLineItemReferenceCrudService->createCreateCrudRequest($item);
            $references[] = $this->offerLineItemReferenceCrudService->addLineItem(
                $offer->listId,
                $offer->id,
                $crudService,
                $currencyContext,
                $identity
            );
        }

        $lineItemList = $this->offerLineItemListRepository->fetchOneListById(
            $offer->listId,
            $currencyContext,
            $identity->getOwnershipContext()
        );

        return ['success' => true, 'offer' => $offer, 'lineItemList' => $lineItemList, 'items' => $references];
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @param Request $request
     * @return array
     */
    public function removeItemsAction(string $debtorEmail, int $offerId, Request $request): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->contextIdentityLoader->fetchIdentityByEmail($debtorEmail, $this->loginContextService);

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());
        $itemIds = $request->getPost();

        $items = $this->offerLineItemListRepository
            ->fetchOneListById($offer->listId, $currencyContext, $identity->getOwnershipContext())
            ->references;

        $references = [];
        foreach ($items as $item) {
            if (in_array($item->id, $itemIds, true)) {
                $item->id = null;
                $references[] = $item;
            }
        }

        foreach ($itemIds as $itemId) {
            $this->offerLineItemReferenceCrudService->deleteLineItem(
                $offer->id,
                $offer->listId,
                $itemId,
                $currencyContext,
                $identity
            );
        }

        $lineItemList = $this->offerLineItemListRepository->fetchOneListById(
            $offer->listId,
            $currencyContext,
            $identity->getOwnershipContext()
        );

        return ['success' => true, 'orderList' => $offer, 'lineItemList' => $lineItemList, 'items' => $references];
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @param Request $request
     * @return array
     */
    public function updateItemsAction(string $debtorEmail, int $offerId, Request $request): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $identity = $this->contextIdentityLoader->fetchIdentityByEmail($debtorEmail, $this->loginContextService);

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());
        $items = $request->getPost();

        $references = [];
        foreach ($items as $item) {
            $item['discountAmountNet'] = (float) $item['discountAmountNet'];
            $crudService =$this->offerLineItemReferenceCrudService->createUpdateCrudRequest($item);
            $references[] = $this->offerLineItemReferenceCrudService
                ->updateLineItem($offer->listId, $offer->id, $crudService, $currencyContext, $identity);
        }

        $lineItemList = $this->offerLineItemListRepository->fetchOneListById(
            $offer->listId,
            $currencyContext,
            $identity->getOwnershipContext()
        );

        return ['success' => true, 'offer' => $offer, 'lineItemList' => $lineItemList, 'items' => $references];
    }

    /**
     * @param string $debtorEmail
     * @param int $offerId
     * @return array
     */
    public function getItemsAction(string $debtorEmail, int $offerId): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $offer = $this->offerRepository->fetchOfferById($offerId, $currencyContext, $ownershipContext);

        $lineItemList = $this->offerLineItemListRepository->fetchOneListById(
            $offer->listId,
            $currencyContext,
            $ownershipContext
        );

        return ['success' => true, 'offer' => $offer, 'lineItemList' => $lineItemList, 'items' => $lineItemList->references];
    }
}
