<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Enlight\Event\SubscriberInterface;
use Shopware\B2B\Cart\Bridge\CartAccessSubscriber;
use Shopware\B2B\Cart\Framework\CartStateInterface;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OfferCartAccessSubscriber implements SubscriberInterface
{
    /**
     * @var CartStateInterface
     */
    private $cartState;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param CartStateInterface $cartState
     * @param OfferRepository $offerRepository
     * @param CurrencyService $currencyService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        CartStateInterface $cartState,
        OfferRepository $offerRepository,
        CurrencyService $currencyService,
        AuthenticationService $authenticationService
    ) {
        $this->cartState = $cartState;
        $this->offerRepository = $offerRepository;
        $this->currencyService = $currencyService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CartAccessSubscriber::EVENT_NAME => 'clearOffer',
        ];
    }

    /**
     * @param $args
     */
    public function clearOffer($args)
    {
        if (!$this->cartState->hasStateId()) {
            return;
        }

        $oldOrderContextId = $this->cartState->getStateId();

        if (($this->cartState->isState('offer') || $this->cartState->isState('offerCheckoutEnabled'))) {
            return;
        }

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            $offer = $this->offerRepository->fetchOfferByOrderContextId($oldOrderContextId, $this->currencyService->createCurrencyContext(), $ownershipContext);
        } catch (NotFoundException $e) {
            return;
        }

        $this->offerRepository->removeOfferWithoutContext($offer);
    }
}
