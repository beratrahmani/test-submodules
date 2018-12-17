<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

use Shopware\B2B\AclRoute\Framework\AclRouteService;
use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartStateInterface;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Bridge\LineItemCheckoutSource;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\Order\Framework\OrderContextService;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceEntity;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class CartAccessModeOrderClearance implements CartAccessModeInterface
{
    const NAME = 'clearance';

    /**
     * @var LineItemList
     */
    private $lineItemList;

    /**
     * @var OrderClearanceEntity
     */
    private $orderClearance;

    /**
     * @var OrderClearanceRepositoryInterface
     */
    private $orderClearanceRepository;

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
     * @var CartStateInterface
     */
    private $cartState;

    /**
     * @var AclRouteService
     */
    private $aclRouteService;
    
    /**
     * @var \sBasket
     */
    private $basket;

    /**
     * @param OrderClearanceRepositoryInterface $orderClearanceRepository
     * @param OrderContextRepository $orderContextRepository
     * @param LineItemListService $lineItemListService
     * @param OrderContextService $orderContextService
     * @param CurrencyService $currencyService
     * @param CartStateInterface $cartState
     * @param AclRouteService $aclRouteService
     * @param \sBasket $basket
     */
    public function __construct(
        OrderClearanceRepositoryInterface $orderClearanceRepository,
        OrderContextRepository $orderContextRepository,
        LineItemListService $lineItemListService,
        OrderContextService $orderContextService,
        CurrencyService $currencyService,
        CartStateInterface $cartState,
        AclRouteService $aclRouteService,
        \sBasket $basket
    ) {
        $this->orderClearanceRepository = $orderClearanceRepository;
        $this->orderContextRepository = $orderContextRepository;
        $this->lineItemListService = $lineItemListService;
        $this->orderContextService = $orderContextService;
        $this->currencyService = $currencyService;
        $this->cartState = $cartState;
        $this->aclRouteService = $aclRouteService;
        $this->basket = $basket;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'clearance';
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(): bool
    {
        //TODO refactor getParam -> CartState
        return ($this->cartState
                ->hasStateId() && ($this->cartState->isState($this->getName()) || $this->cartState->isState('orderClearanceEnabled')))
            && !Shopware()->Front()->Request()->getParam('B2bOffer', false);
    }

    /**
     * {@inheritdoc}
     */
    public function enable(OwnershipContext $ownershipContext)
    {
        $orderContextId = $this->cartState
            ->getStateId();

        $currencyContext = $this->currencyService->createCurrencyContext();

        $orderClearance = $this->orderClearanceRepository
            ->fetchOneByOrderContextId($orderContextId, $currencyContext, $ownershipContext);

        $this->orderClearance = $orderClearance;

        $this->lineItemList = $this->orderClearance->list;

        if ($this->cartState->isState('orderClearanceEnabled')) {
            if ($this->aclRouteService->isRouteAllowed('B2bOrderLineItemReference', 'updateLineItem')) {
                $this->lineItemListService
                    ->updateListThroughCheckoutSource(
                        $orderClearance->list,
                        new LineItemCheckoutSource($this->basket->sGetBasket()),
                        $ownershipContext
                    );
            }

            return;
        }

        $this->lineItemListService
            ->produceCart($orderClearance->listId, $currencyContext, $ownershipContext);

        $this->orderContextService
            ->extendCart($orderClearance);

        $this->lineItemListService
            ->updateListThroughCheckoutSource(
                $orderClearance->list,
                new LineItemCheckoutSource($this->basket->sGetBasket()),
                $ownershipContext
            );

        $this->cartState->setState('orderClearanceEnabled');
    }

    /**
     * {@inheritdoc}
     */
    public function handleOrder(Identity $identity, CartAccessResult $cartAccessResult)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handleCreatedOrder(string $orderNumber, OwnershipContext $ownershipContext)
    {
        $order = Shopware()->Modules()->Order();

        $this->lineItemListService
            ->updateListThroughCheckoutSource(
                $this->orderClearance->list,
                new LineItemCheckoutSource($order->sBasketData),
                $ownershipContext
            );

        $this->syncOrderData($order, $orderNumber);

        $this->orderContextRepository->syncFinishOrder($this->orderClearance);

        $this->cartState->resetStateId();
        $this->cartState->resetState();
        $this->cartState->resetOldState();
    }

    /**
     * @internal
     * @param \sOrder $order
     * @param string $orderNumber
     */
    protected function syncOrderData(\sOrder $order, string $orderNumber)
    {
        $this->orderClearance->orderNumber = $orderNumber;
        $this->orderClearance->shippingId = (int) $order->dispatchId;
        $this->orderClearance->shippingAmount = (float) $order->sBasketData['sShippingcosts'];
        $this->orderClearance->shippingAmountNet = (float) $order->sBasketData['sShippingcostsNet'];
        $this->orderClearance->paymentId = (int) $order->sUserData['additional']['payment']['id'];
        $this->orderClearance->billingAddressId = (int) $order->sUserData['billingaddress']['id'];
        $this->orderClearance->shippingAddressId = (int) $order->sUserData['shippingaddress']['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderContext(): OrderContext
    {
        if (!$this->orderClearance) {
            throw new NotFoundException('order context not set');
        }

        return $this->orderClearance;
    }
}
