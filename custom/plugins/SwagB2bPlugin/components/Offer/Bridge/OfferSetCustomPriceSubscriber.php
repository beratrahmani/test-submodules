<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Cart\Framework\CartStateInterface;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceRepository;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\Order\Framework\OrderContextRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OfferSetCustomPriceSubscriber implements SubscriberInterface
{
    /**
     * @var OfferLineItemReferenceRepository
     */
    private $offerLineItemReferenceRepository;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var OrderContextRepository
     */
    private $orderContextRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var CartStateInterface
     */
    private $cartState;

    /**
     * @param CartStateInterface $cartState
     * @param OfferLineItemReferenceRepository $offerLineItemReferenceRepository
     * @param OfferRepository $offerRepository
     * @param CurrencyService $currencyService
     * @param OrderContextRepository $orderContextRepository
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        CartStateInterface $cartState,
        OfferLineItemReferenceRepository $offerLineItemReferenceRepository,
        OfferRepository $offerRepository,
        CurrencyService $currencyService,
        OrderContextRepository $orderContextRepository,
        AuthenticationService $authenticationService
    ) {
        $this->offerLineItemReferenceRepository = $offerLineItemReferenceRepository;
        $this->offerRepository = $offerRepository;
        $this->currencyService = $currencyService;
        $this->orderContextRepository = $orderContextRepository;
        $this->cartState = $cartState;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware_Modules_Basket_getPriceForUpdateArticle_FilterPrice' => 'setCustomPriceForArticle',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return array
     */
    public function setCustomPriceForArticle(\Enlight_Event_EventArgs $args): array
    {
        $return = $args->getReturn();

        if (!$this->authenticationService->isB2b()) {
            return $return;
        }

        $quantity = (int) $args->get('quantity');

        if (!$this->cartState->hasStateId()) {
            return $return;
        }

        $orderContextId = $this->cartState->getStateId();

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            $this->offerRepository->fetchOfferByOrderContextId($orderContextId, $this->currencyService->createCurrencyContext(), $ownershipContext);
        } catch (NotFoundException $e) {
            return $return;
        }

        $orderContext = $this->orderContextRepository->fetchOneOrderContextById($orderContextId, $ownershipContext);

        try {
            $reference = $this->offerLineItemReferenceRepository
                ->fetchReferenceByReferenceNumberAndListIdAndQuantity(
                    $return['ordernumber'],
                    $orderContext->listId,
                    $quantity,
                    $ownershipContext
                );

            $return['price'] = $reference->discountAmountNet;
        } catch (NotFoundException $e) {
            //nth
        }

        return $return;
    }
}
