<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Bridge;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_Hook_HookArgs;
use Shopware\B2B\Cart\Framework\CartStateInterface;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Offer\Framework\ActiveOfferException;
use Shopware\B2B\Offer\Framework\NoActiveOfferException;
use Shopware\B2B\Offer\Framework\OfferEntity;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceEntity;
use Shopware\B2B\Offer\Framework\OfferLineItemReferenceRepository;
use Shopware\B2B\Offer\Framework\OfferRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class OfferArticleSubscriber implements SubscriberInterface
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
     * @var BasketOfferRepository
     */
    private $basketOfferRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

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
     * @param BasketOfferRepository $basketOfferRepository
     * @param CurrencyService $currencyService
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        CartStateInterface $cartState,
        OfferLineItemReferenceRepository $offerLineItemReferenceRepository,
        OfferRepository $offerRepository,
        BasketOfferRepository $basketOfferRepository,
        CurrencyService $currencyService,
        AuthenticationService $authenticationService
    ) {
        $this->offerLineItemReferenceRepository = $offerLineItemReferenceRepository;
        $this->offerRepository = $offerRepository;
        $this->basketOfferRepository = $basketOfferRepository;
        $this->currencyService = $currencyService;
        $this->authenticationService = $authenticationService;
        $this->cartState = $cartState;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'sBasket::clearBasket::before' => 'preventClearBasket',
            'sBasket::sDeleteArticle::before' => 'preventDeleteOfferItem',
            'sBasket::sDeleteBasket::before' => 'preventClearBasket',
            'sBasket::sUpdateArticle::before' => 'preventUpdateArticle',
            'Shopware_Modules_Basket_GetBasket_FilterResult' => 'preventOfferChanges',
        ];
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     * @throws ActiveOfferException
     * @return Enlight_Hook_HookArgs
     */
    public function preventUpdateArticle(Enlight_Hook_HookArgs $args)
    {
        try {
            $this->isOfferCheckoutEnabled();
            $offer = $this->getOffer();
        } catch (NoActiveOfferException $exception) {
            return $args;
        }

        $arguments = $args->getArgs();

        $article = $this->basketOfferRepository->fetchArticleById((int) $arguments[0]);
        if ($article['ordernumber'] === OfferEntity::DISCOUNT_REFERENCE) {
            throw new ActiveOfferException('You can not delete the discount of an offer, while it is in progress.');
        }

        if (!$this->offerLineItemReferenceRepository->hasReference($article['ordernumber'], $offer->listId)) {
            return $args;
        }

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        /** @var OfferLineItemReferenceEntity $reference */
        $reference = $this->offerLineItemReferenceRepository->fetchReferenceByReferenceNumberAndListId(
            $article['ordernumber'],
            $offer->listId,
            $ownershipContext
        );

        if ($reference->quantity <= $arguments[1]) {
            return $args;
        }

        throw new ActiveOfferException('You can not delete an article of an offer, while it is in progress.');
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     * @throws ActiveOfferException
     */
    public function preventClearBasket(Enlight_Hook_HookArgs $args)
    {
        try {
            $this->isOfferCheckoutEnabled();
            $this->getOffer();
        } catch (NoActiveOfferException $exception) {
            return;
        }

        throw new ActiveOfferException('An Offer is in progress.');
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     * @throws ActiveOfferException
     * @return Enlight_Hook_HookArgs|void
     */
    public function preventDeleteOfferItem(Enlight_Hook_HookArgs $args)
    {
        try {
            $this->isOfferCheckoutEnabled();
            $offer = $this->getOffer();
        } catch (NoActiveOfferException $exception) {
            return $args;
        }

        $arguments = $args->getArgs();

        $article = $this->basketOfferRepository->fetchArticleById((int) $arguments[0]);

        if ($article['ordernumber'] === OfferEntity::DISCOUNT_REFERENCE
            || $this->offerLineItemReferenceRepository->hasReference($article['ordernumber'], $offer->listId)
        ) {
            throw new ActiveOfferException('You can not delete a part of an offer, while it is in progress.');
        }

        return $args;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function preventOfferChanges(Enlight_Event_EventArgs $args)
    {
        try {
            $this->isOfferCheckoutEnabled();
            $offer = $this->getOffer();
        } catch (NoActiveOfferException $exception) {
            return;
        }

        $return = $args->getReturn();

        $articles = $return['content'];

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $references = $this->offerLineItemReferenceRepository->fetchAllForList($offer->listId, $ownershipContext);

        foreach ($articles as &$article) {
            if ($article['ordernumber'] === OfferEntity::DISCOUNT_REFERENCE) {
                $article['maxpurchase'] = 1;
                $article['quantity'] = 1;
                $article['erasable'] = false;
                continue;
            }

            foreach ($references as $reference) {
                if ($reference->referenceNumber !== $article['ordernumber']) {
                    continue;
                }

                $article['minpurchase'] = $reference->quantity;
                $article['erasable'] = false;
                break;
            }
        }

        $return['content'] = $articles;

        $args->setReturn($return);
    }

    /**
     * @throws NoActiveOfferException
     * @return OfferEntity
     */
    private function getOffer(): OfferEntity
    {
        if (!$this->cartState->hasStateId()) {
            throw new NoActiveOfferException();
        }

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            $offer = $this->offerRepository->fetchOfferByOrderContextId($this->cartState->getStateId(), $this->currencyService->createCurrencyContext(), $ownershipContext);
        } catch (NotFoundException $exception) {
            throw new NoActiveOfferException();
        }

        return $offer;
    }

    /**
     * @throws NoActiveOfferException
     * @return bool
     */
    private function isOfferCheckoutEnabled(): bool
    {
        if (!$this->authenticationService->isB2b()) {
            throw new NoActiveOfferException();
        }

        if (!($this->cartState->isState(CartAccessModeOfferCheckout::NAME) || $this->cartState->isState('offerCheckoutEnabled'))) {
            throw new NoActiveOfferException();
        }

        return true;
    }
}
