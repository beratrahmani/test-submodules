<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Bridge;

use Shopware\B2B\Address\Framework\AddressCheckoutServiceInterface;
use Shopware\B2B\Address\Framework\AddressEntity;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;

/**
 * @see \Shopware_Controllers_Frontend_Address
 */
class AddressCheckoutService implements AddressCheckoutServiceInterface
{
    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var ContextServiceInterface
     */
    private $storefrontContextService;

    /**
     * @var array
     */
    private $typeMap = [
        'billing' => 'checkoutBillingAddressId',
        'shipping' => 'checkoutShippingAddressId',
    ];

    /**
     * @var CountryRepository
     */
    private $countryRepository;

    /**
     * @param \Enlight_Components_Session_Namespace $session
     * @param ContextServiceInterface $storefrontContextService
     * @param CountryRepository $countryRepository
     */
    public function __construct(
        \Enlight_Components_Session_Namespace $session,
        ContextServiceInterface $storefrontContextService,
        CountryRepository $countryRepository
    ) {
        $this->session = $session;
        $this->storefrontContextService = $storefrontContextService;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @param AddressEntity $address
     * @param string $type
     */
    public function updateCheckoutAddress(string $type, AddressEntity $address)
    {
        $sessionKey = $this->typeMap[$type];
        $this->session->offsetSet($sessionKey, $address->id);
        $this->refreshSession($address);
    }

    /**
     * @param AddressEntity $address
     */
    private function refreshSession(AddressEntity $address)
    {
        $countryId = $address->country_id;
        $stateId = $address->state_id;

        $areaId = null;

        if ($countryId) {
            $areaId = $this->countryRepository->fetchAreaIdForCountryId($countryId);
        }

        $this->session->offsetSet('sCountry', $countryId);
        $this->session->offsetSet('sState', $stateId);
        $this->session->offsetSet('sArea', $areaId);

        $this->storefrontContextService->initializeShopContext();
    }
}
