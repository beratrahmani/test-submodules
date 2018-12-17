<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\LineItemList\Bridge\LineItemCheckoutSource;
use Shopware\B2B\LineItemList\Framework\LineItemList;
use Shopware\B2B\LineItemList\Framework\LineItemListService;
use Shopware\B2B\Offer\Framework\CreateOfferThroughCartInterface;
use Shopware\B2B\Offer\Framework\OfferContextRepository;
use Shopware\B2B\Offer\Framework\OfferEntity;
use Shopware\B2B\Offer\Framework\OfferService;
use Shopware\B2B\Order\Bridge\OrderCheckoutSource;
use Shopware\B2B\Order\Framework\OrderContext;
use Shopware\B2B\Order\Framework\OrderContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class CreateOfferThroughCart implements CreateOfferThroughCartInterface
{
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
     * @var OrderContextService
     */
    private $offerContextRepository;

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * @param LineItemListService $lineItemListService
     * @param OrderContextService $orderContextService
     * @param CurrencyService $currencyService
     * @param OfferContextRepository $offerContextRepository
     * @param OfferService $offerService
     */
    public function __construct(
        LineItemListService $lineItemListService,
        OrderContextService $orderContextService,
        OfferContextRepository $offerContextRepository,
        OfferService $offerService
    ) {
        $this->lineItemListService = $lineItemListService;
        $this->orderContextService = $orderContextService;
        $this->offerContextRepository = $offerContextRepository;
        $this->offerService = $offerService;
    }

    /**
     * {@inheritdoc}
     */
    public function createOffer(Identity $identity, CurrencyContext $currencyContext): OfferEntity
    {
        $this->lineItemList = $this->createList($identity);
        $this->orderContext = $this->createOrderContext($identity);
        $offer = $this->offerService->createOfferThroughCheckoutSource(
            $identity,
            $currencyContext,
            $this->orderContext,
            $this->lineItemList
        );

        $this->offerContextRepository
            ->sendToOfferState($this->orderContext->id, $identity->getOwnershipContext());

        Shopware()->Modules()->Basket()->clearBasket();

        return $offer;
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
     * @param Identity $identity
     * @return OrderContext
     */
    private function createOrderContext(Identity $identity): OrderContext
    {
        $checkoutOrderSource = new OrderCheckoutSource(
            Shopware()->Modules()->Basket()->sGetBasketData(),
            (int) Shopware()->Modules()->Admin()->sGetUserData()['billingaddress']['id'],
            (int) Shopware()->Modules()->Admin()->sGetUserData()['shippingaddress']['id'],
            0,
            '',
            (string) Shopware()->Front()->Request()->getDeviceType()
        );

        return $this->orderContextService
            ->createContextThroughCheckoutSource(
                $identity->getOwnershipContext(),
                $this->lineItemList,
                $checkoutOrderSource
            );
    }
}
