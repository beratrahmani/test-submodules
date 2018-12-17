<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Shopware\B2B\Cart\Bridge\CartAccessModeInterface;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartStateInterface;
use Shopware\B2B\Common\Controller\B2bControllerRedirectException;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Bridge\LineItemCheckoutSource;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\Offer\Framework\OfferContextRepository;
use Shopware\B2B\Offer\Framework\OfferEntity;
use Shopware\B2B\Offer\Framework\OfferLineItemAuditLogService;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceRepository;
use Shopware\B2B\Offer\Framework\OfferService;
use Shopware\B2B\Order\Bridge\OrderCheckoutSource;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\Order\Framework\OrderContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class CartAccessModeOffer implements CartAccessModeInterface
{
    const NAME = 'offerSetter';

    /**
     * @var LineItemList
     */
    private $lineItemList;

    /**
     * @var OrderContext
     */
    private $orderContext;

    /**
     * @var LineItemListService
     */
    private $lineItemListService;

    /**
     * @var OrderContextService
     */
    private $orderContextService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var OfferContextRepository
     */
    private $offerContextRepository;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @var OfferLineItemAuditLogService
     */
    private $auditLogService;

    /**
     * @var CartStateInterface
     */
    private $cartState;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var OfferLineItemReferenceRepository
     */
    private $offerLineItemReferenceRepository;

    /**
     * @param LineItemListService $lineItemListService
     * @param OrderContextService $orderContextService
     * @param CurrencyService $currencyService
     * @param OfferContextRepository $offerContextRepository
     * @param OfferService $offerService
     * @param OfferLineItemAuditLogService $auditLogService
     * @param CartStateInterface $cartState
     * @param LineItemListRepository $lineItemListRepository
     * @param OrderContextRepository $orderContextRepository
     * @param OfferLineItemReferenceRepository $offerLineItemReferenceRepository
     */
    public function __construct(
        LineItemListService $lineItemListService,
        OrderContextService $orderContextService,
        CurrencyService $currencyService,
        OfferContextRepository $offerContextRepository,
        OfferService $offerService,
        OfferLineItemAuditLogService $auditLogService,
        CartStateInterface $cartState,
        LineItemListRepository $lineItemListRepository,
        OrderContextRepository $orderContextRepository,
        OfferLineItemReferenceRepository $offerLineItemReferenceRepository
    ) {
        $this->lineItemListService = $lineItemListService;
        $this->orderContextService = $orderContextService;
        $this->currencyService = $currencyService;
        $this->offerContextRepository = $offerContextRepository;
        $this->offerService = $offerService;
        $this->auditLogService = $auditLogService;
        $this->cartState = $cartState;
        $this->lineItemListRepository = $lineItemListRepository;
        $this->orderContextRepository = $orderContextRepository;
        $this->offerLineItemReferenceRepository = $offerLineItemReferenceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(): bool
    {
        $isB2bOffer = (bool) Shopware()->Front()->Request()->getParam('B2bOffer', false);

        if ($isB2bOffer) {
            $this->cartState->setState('offer');
        }

        return $isB2bOffer;
    }

    /**
     * {@inheritdoc}
     */
    public function enable(OwnershipContext $ownershipContext)
    {
        Shopware()->Front()->Request()->setParam('sAGB', true);
    }

    /**
     * {@inheritdoc}
     */
    public function handleOrder(Identity $identity, CartAccessResult $cartAccessResult)
    {
        $currencyContext = $this->currencyService->createCurrencyContext();

        if ($this->cartState->hasStateId()) {
            $this->orderContext = $this->updateOrderContext($identity);
        } else {
            $this->orderContext = $this->createOrderContext($identity);

            foreach ($this->lineItemList->references as $reference) {
                $this->auditLogService->createAddLineItem($this->lineItemList->id, $reference);
            }
        }

        $offer = $this->createOffer($identity, $currencyContext);

        $this->offerContextRepository
            ->sendToOfferState($this->orderContext->id, $identity->getOwnershipContext());

        Shopware()->Modules()->Basket()->clearBasket();
        throw new B2bControllerRedirectException('index', 'b2bofferthroughcheckout', null, ['offerId' => $offer->id]);
    }

    /**
     * {@inheritdoc}
     */
    public function handleCreatedOrder(string $orderNumber, OwnershipContext $ownershipContext)
    {
        $this->cartState->resetStateId();
        $this->cartState->resetState();
        $this->cartState->resetOldState();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderContext(): OrderContext
    {
        if (!$this->orderContext) {
            throw new NotFoundException('order context not set');
        }

        return $this->orderContext;
    }

    /**
     * @param Identity $identity
     * @return LineItemList
     */
    private function createList(Identity $identity): LineItemList
    {
        foreach (Shopware()->Modules()->Basket()->sGetBasketData()['content'] as $row) {
            if (!$row['modus']) {
                continue;
            }

            Shopware()->Modules()->Basket()->sDeleteArticle($row['id']);
        }

        $checkoutListSource = new LineItemCheckoutSource(
            Shopware()->Modules()->Basket()->sGetBasket()
        );

        return $this->lineItemListService
            ->createListThroughCheckoutSource(
                $identity->getOwnershipContext(),
                $checkoutListSource
            );
    }

    /**
     * @internal
     * @param Identity $identity
     * @return OrderContext
     */
    protected function updateOrderContext(Identity $identity): OrderContext
    {
        $orderContext = $this->orderContextRepository->fetchOneOrderContextById($this->cartState->getStateId(), $identity->getOwnershipContext());

        $lineItemList = $this->lineItemListRepository->fetchOneListById($orderContext->listId, $this->currencyService->createCurrencyContext(), $identity->getOwnershipContext());

        $lineItemList->references = $this->offerLineItemReferenceRepository->fetchAllForList($lineItemList->id, $identity->getOwnershipContext());

        $checkoutListSource = new LineItemCheckoutSource(
            Shopware()->Modules()->Basket()->sGetBasket()
        );

        $this->lineItemList = $this->lineItemListService->updateListThroughCheckoutSource($lineItemList, $checkoutListSource, $identity->getOwnershipContext());

        $checkoutOrderSource = new OrderCheckoutSource(
            Shopware()->Modules()->Order()->sBasketData,
            (int) Shopware()->Modules()->Order()->sUserData['billingaddress']['id'],
            (int) Shopware()->Modules()->Order()->sUserData['shippingaddress']['id'],
            (int) Shopware()->Modules()->Order()->dispatchId,
            (string) Shopware()->Modules()->Order()->sComment,
            (string) Shopware()->Modules()->Order()->deviceType
        );

        return $this->orderContextService
            ->updateOrderContextThroughCheckoutSource(
                $identity->getOwnershipContext(),
                $this->lineItemList,
                $checkoutOrderSource,
                $orderContext
            );
    }

    /**
     * @internal
     * @param Identity $identity
     * @return OrderContext
     */
    protected function createOrderContext(Identity $identity): OrderContext
    {
        $this->lineItemList = $this->createList($identity);

        $checkoutOrderSource = new OrderCheckoutSource(
            Shopware()->Modules()->Order()->sBasketData,
            (int) Shopware()->Modules()->Order()->sUserData['billingaddress']['id'],
            (int) Shopware()->Modules()->Order()->sUserData['shippingaddress']['id'],
            (int) Shopware()->Modules()->Order()->dispatchId,
            (string) Shopware()->Modules()->Order()->sComment,
            (string) Shopware()->Modules()->Order()->deviceType
        );

        return $this->orderContextService
            ->createContextThroughCheckoutSource(
                $identity->getOwnershipContext(),
                $this->lineItemList,
                $checkoutOrderSource
            );
    }

    /**
     * @param Identity $identity
     * @param CurrencyContext $currencyContext
     * @return OfferEntity
     */
    private function createOffer(
        Identity $identity,
        CurrencyContext $currencyContext
    ): OfferEntity {
        return $this->offerService->createOfferThroughCheckoutSource(
            $identity,
            $currencyContext,
            $this->orderContext,
            $this->lineItemList
        );
    }
}
