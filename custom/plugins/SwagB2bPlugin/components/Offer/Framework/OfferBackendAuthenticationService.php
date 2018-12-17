<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class OfferBackendAuthenticationService
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param OfferRepository $offerRepository
     */
    public function __construct(AuthenticationService $authenticationService, OfferRepository $offerRepository)
    {
        $this->authenticationService = $authenticationService;
        $this->offerRepository = $offerRepository;
    }

    /**
     * @param $offerId
     * @return Identity
     */
    public function getIdentityByOfferId(int $offerId): Identity
    {
        $authId = $this->offerRepository->fetchAuthIdFromOfferById($offerId);
        $identity = $this->authenticationService->getIdentityByAuthId($authId);

        return $identity;
    }
}
