<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Offer\Framework\OfferBackendAuthenticationService;
use Shopware\B2B\Offer\Framework\OfferRepository;

class DiscountSubscriber implements SubscriberInterface
{
    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var BasketOfferRepository
     */
    private $basketOfferRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var OfferBackendAuthenticationService
     */
    private $authenticationService;

    /**
     * @param Enlight_Components_Session_Namespace $session
     * @param OfferRepository $offerRepository
     * @param BasketOfferRepository $basketOfferRepository
     * @param CurrencyService $currencyService
     * @param OfferBackendAuthenticationService $authenticationService
     */
    public function __construct(
        Enlight_Components_Session_Namespace $session,
        OfferRepository $offerRepository,
        BasketOfferRepository $basketOfferRepository,
        CurrencyService $currencyService,
        OfferBackendAuthenticationService $authenticationService
    ) {
        $this->session = $session;
        $this->offerRepository = $offerRepository;
        $this->basketOfferRepository = $basketOfferRepository;
        $this->currencyService = $currencyService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'sBasket::sGetBasketData::before' => 'changeDiscount',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function changeDiscount(\Enlight_Event_EventArgs $args)
    {
        $id = (int) $this->session
            ->get('offerEntityId', false);

        if (!$id) {
            return;
        }

        $identity = $this->authenticationService->getIdentityByOfferId($id);

        $currencyContext = $this->currencyService->createCurrencyContext();

        $offer = $this->offerRepository->fetchOfferById($id, $currencyContext, $identity->getOwnershipContext());

        $this->basketOfferRepository->addDiscountToBasket($offer, $identity->getOwnershipContext());
    }
}
