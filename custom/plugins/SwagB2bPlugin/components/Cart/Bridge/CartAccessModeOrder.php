<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge;

use Shopware\B2B\Cart\Framework\CartAccessResult;
use Shopware\B2B\Cart\Framework\CartStateInterface;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\LineItemList\Bridge\LineItemCheckoutSource;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListRepository;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\Order\Bridge\OrderCheckoutSource;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\Order\Framework\OrderContextService;
use Shopware\B2B\OrderClearance\Framework\OrderClearanceRepositoryInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class CartAccessModeOrder implements CartAccessDefaultModeInterface
{
    const NAME = 'order';

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
     * @var OrderClearanceRepositoryInterface
     */
    private $orderClearanceRepository;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var CartStateInterface
     */
    private $cartState;

    /**
     * @var LineItemListRepository
     */
    private $lineItemListRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @param LineItemListService $lineItemListService
     * @param OrderContextService $orderContextService
     * @param OrderContextRepository $orderContextRepository
     * @param OrderClearanceRepositoryInterface $orderClearanceRepository
     * @param CartStateInterface $cartState
     * @param LineItemListRepository $lineItemListRepository
     * @param CurrencyService $currencyService
     */
    public function __construct(
        LineItemListService $lineItemListService,
        OrderContextService $orderContextService,
        OrderContextRepository $orderContextRepository,
        OrderClearanceRepositoryInterface $orderClearanceRepository,
        CartStateInterface $cartState,
        LineItemListRepository $lineItemListRepository,
        CurrencyService $currencyService
    ) {
        $this->lineItemListService = $lineItemListService;
        $this->orderContextService = $orderContextService;
        $this->orderClearanceRepository = $orderClearanceRepository;
        $this->orderContextRepository = $orderContextRepository;
        $this->cartState = $cartState;
        $this->lineItemListRepository = $lineItemListRepository;
        $this->currencyService = $currencyService;
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
    public function enable(OwnershipContext $ownershipContext)
    {
        $this->cartState->setState($this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function handleOrder(Identity $identity, CartAccessResult $cartAccessResult)
    {
        $this->changeOrder($identity);

        if (!$cartAccessResult->hasErrors()) {
            return;
        }

        $this->orderClearanceRepository
            ->sendToOrderClearance($this->orderContext->id, $identity->getOwnershipContext());

        $this->orderContext = $this->orderContextRepository
            ->fetchOneOrderContextById($this->orderContext->id, $identity->getOwnershipContext());

        Shopware()->Modules()->Basket()->clearBasket();
        $this->cartState->resetState();
        $this->cartState->resetOldState();
        throw new B2bControllerForwardException('finish', 'b2bcart');
    }

    /**
     * @internal
     * @param Identity $identity
     */
    protected function changeOrder(Identity $identity)
    {
        if ($this->cartState->hasStateId()) {
            $this->orderContext = $this->updateOrderContext($identity);
        } else {
            $this->lineItemList = $this->createList($identity);
            $this->orderContext = $this->createOrderContext($identity);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleCreatedOrder(string $orderNumber, OwnershipContext $ownershipContext)
    {
        $order = Shopware()->Modules()->Order();

        $this->syncOrderData($order, $orderNumber);

        $this->orderContextRepository->syncFinishOrder($this->orderContext);
    }

    /**
     * @internal
     * @param \sOrder $order
     * @param string $orderNumber
     */
    protected function syncOrderData(\sOrder $order, string $orderNumber)
    {
        $this->orderContext->orderNumber = $orderNumber;
        $this->orderContext->shippingId = (int) $order->dispatchId;
        $this->orderContext->shippingAmount = (float) $order->sBasketData['sShippingcostsWithTax'];
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
        $checkoutListSource = new LineItemCheckoutSource(
            Shopware()->Modules()->Order()->sBasketData
        );

        return $this->lineItemListService
            ->createListThroughCheckoutSource(
                $identity->getOwnershipContext(),
                $checkoutListSource
            );
    }

    /**
     * @internal
     * @param LineItemList $lineItemList
     * @param OwnershipContext $ownershipContext
     * @return LineItemList
     */
    protected function updateList(LineItemList $lineItemList, OwnershipContext $ownershipContext): LineItemList
    {
        $checkoutListSource = new LineItemCheckoutSource(
                Shopware()->Modules()->Order()->sBasketData
        );

        return $this->lineItemListService
                ->updateListThroughCheckoutSource(
                    $lineItemList,
                    $checkoutListSource,
                    $ownershipContext
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

        $this->lineItemList = $this->updateList($lineItemList, $identity->getOwnershipContext());

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
     * @param Identity $identity
     * @return OrderContext
     */
    private function createOrderContext(Identity $identity): OrderContext
    {
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
}
