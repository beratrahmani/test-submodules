<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Shopware\B2B\Cart\Bridge\CartAccessModeInterface;
use Shopware\B2B\Cart\Bridge\CartAccessModeOrder;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartStateInterface;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Bridge\LineItemCheckoutSource;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\Offer\Framework\OfferEntity;
use Shopware\B2B\Offer\Framework\OfferLineItemListRepository;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\Order\Framework\OrderContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use sOrder;

class CartAccessModeOfferCheckout implements CartAccessModeInterface
{
    const NAME = 'offerCheckout';

    /**
     * @var LineItemList
     */
    private $lineItemList;

    /**
     * @var OfferEntity
     */
    private $offerEntity;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

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
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferLineItemListRepository
     */
    private $offerLineItemListRepository;

    /**
     * @var OrderContext
     */
    private $orderContext;

    /**
     * @var BasketOfferRepository
     */
    private $basketOfferRepository;

    /**
     * @var CartStateInterface
     */
    private $cartState;

    /**
     * @var CartAccessModeOrder
     */
    private $cartAccessModeOrder;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param OrderContextRepository $orderContextRepository
     * @param LineItemListService $lineItemListService
     * @param OrderContextService $orderContextService
     * @param CurrencyService $currencyService
     * @param OfferRepository $offerRepository
     * @param OfferLineItemListRepository $offerLineItemListRepository
     * @param BasketOfferRepository $basketOfferRepository
     * @param CartStateInterface $cartState
     * @param CartAccessModeOrder $cartAccessModeOrder
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        OrderContextRepository $orderContextRepository,
        LineItemListService $lineItemListService,
        OrderContextService $orderContextService,
        CurrencyService $currencyService,
        OfferRepository $offerRepository,
        OfferLineItemListRepository $offerLineItemListRepository,
        BasketOfferRepository $basketOfferRepository,
        CartStateInterface $cartState,
        CartAccessModeOrder $cartAccessModeOrder,
        AuthenticationService $authenticationService
    ) {
        $this->orderContextRepository = $orderContextRepository;
        $this->lineItemListService = $lineItemListService;
        $this->orderContextService = $orderContextService;
        $this->currencyService = $currencyService;
        $this->offerRepository = $offerRepository;
        $this->offerLineItemListRepository = $offerLineItemListRepository;
        $this->basketOfferRepository = $basketOfferRepository;
        $this->cartState = $cartState;
        $this->cartAccessModeOrder = $cartAccessModeOrder;
        $this->authenticationService = $authenticationService;
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
        if (!$this->cartState->hasStateId() || !($this->cartState->isState($this->getName()) || $this->cartState->isState('offerCheckoutEnabled'))) {
            return false;
        }

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            $this->offerRepository->fetchOfferByOrderContextId($this->cartState->getStateId(), $this->currencyService->createCurrencyContext(), $ownershipContext);
        } catch (NotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function enable(OwnershipContext $ownershipContext)
    {
        $orderContextId = $this->cartState->getStateId();

        $currencyContext = $this->currencyService->createCurrencyContext();

        $this->offerEntity = $this->offerRepository->fetchOfferByOrderContextId($orderContextId, $currencyContext, $ownershipContext);

        $this->orderContext = $this->orderContextRepository->fetchOneOrderContextById(
            $this->offerEntity->orderContextId,
            $ownershipContext
        );

        $this->lineItemList = $this->offerLineItemListRepository->fetchOneListById(
            $this->orderContext->listId,
            $currencyContext,
            $ownershipContext
        );

        if ($this->cartState->isState('offerCheckoutEnabled')) {
            return;
        }

        $this->lineItemListService
            ->produceCart($this->orderContext->listId, $currencyContext, $ownershipContext);

        $this->basketOfferRepository->addDiscountToBasket($this->offerEntity, $ownershipContext);

        $this->orderContextService
            ->extendCart($this->orderContext);

        $this->cartState->setState('offerCheckoutEnabled');
    }

    /**
     * {@inheritdoc}
     */
    public function handleOrder(Identity $identity, CartAccessResult $cartAccessResult)
    {
        if (!$cartAccessResult->hasErrors()) {
            return;
        }

        $this->cartAccessModeOrder->enable($identity->getOwnershipContext());
        $this->cartAccessModeOrder->handleOrder($identity, $cartAccessResult);
    }

    /**
     * {@inheritdoc}
     */
    public function handleCreatedOrder(string $orderNumber, OwnershipContext $ownershipContext)
    {
        $order = Shopware()->Modules()->Order();

        $this->lineItemListService
            ->updateListThroughCheckoutSource(
                $this->lineItemList,
                new LineItemCheckoutSource($order->sBasketData),
                $ownershipContext
        );

        $this->syncOrderData($order, $orderNumber);

        $this->offerEntity->updateDates(['convertedAt']);

        $this->orderContextRepository->syncFinishOrder($this->orderContext);

        $this->offerRepository->updateOfferDates($this->offerEntity);
        $this->offerRepository->updateOffer($this->offerEntity);

        $this->cartState->resetStateId();
        $this->cartState->resetState();
        $this->cartState->resetOldState();
    }

    /**
     * @param sOrder $order
     * @param string $orderNumber
     */
    protected function syncOrderData(sOrder $order, string $orderNumber)
    {
        $this->orderContext->orderNumber = $orderNumber;
        $this->orderContext->shippingId = (int) $order->dispatchId;
        $this->orderContext->shippingAmount = (float) $order->sBasketData['sShippingcosts'];
        $this->orderContext->shippingAmountNet = (float) $order->sBasketData['sShippingcostsNet'];
        $this->orderContext->paymentId = (int) $order->sUserData['additional']['payment']['id'];
        $this->orderContext->billingAddressId = (int) $order->sUserData['billingaddress']['id'];
        $this->orderContext->shippingAddressId = (int) $order->sUserData['shippingaddress']['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderContext(): OrderContext
    {
        return $this->orderContext;
    }
}
