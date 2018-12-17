<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Frontend;

use Shopware\B2B\Common\Controller\B2bControllerRedirectException;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Offer\Framework\CreateOfferThroughCartInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class CreateOfferThroughCartController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var CreateOfferThroughCartInterface
     */
    private $createOfferThroughCart;

    /**
     * @param AuthenticationService $authenticationService
     * @param CurrencyService $currencyService
     * @param CreateOfferThroughCartInterface $createOfferThroughCart
     */
    public function __construct(
        AuthenticationService $authenticationService,
        CurrencyService $currencyService,
        CreateOfferThroughCartInterface $createOfferThroughCart
    ) {
        $this->authenticationService = $authenticationService;
        $this->currencyService = $currencyService;
        $this->createOfferThroughCart = $createOfferThroughCart;
    }

    /**
     * @param Request $request
     * @throws B2bControllerRedirectException
     * @return array
     */
    public function createOfferAction(Request $request): array
    {
        $identity = $this->authenticationService
            ->getIdentity();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $offer = $this->createOfferThroughCart->createOffer($identity, $currencyContext);

        throw new B2bControllerRedirectException('index', 'b2bofferthroughcheckout', null, ['offerId' => $offer->id]);
    }
}
