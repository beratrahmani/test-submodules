<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

use Shopware\B2B\Common\Repository\NotFoundException;

class LoginService
{
    /**
     * @var IdentityChainIdentityLoader
     */
    private $identityChainRepository;

    /**
     * @var AuthStorageAdapterInterface
     */
    private $authStorageAdapter;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @param IdentityChainIdentityLoader $identityChainRepository
     * @param AuthStorageAdapterInterface $authStorageAdapter
     * @param LoginContextService $loginContextService
     */
    public function __construct(
        IdentityChainIdentityLoader $identityChainRepository,
        AuthStorageAdapterInterface $authStorageAdapter,
        LoginContextService $loginContextService
    ) {
        $this->identityChainRepository = $identityChainRepository;
        $this->authStorageAdapter = $authStorageAdapter;
        $this->loginContextService = $loginContextService;
    }

    /**
     * @param CredentialsEntity $credentials
     * @return array
     */
    public function getUserDataBeforeLogin(CredentialsEntity $credentials): array
    {
        $identity = $this->identityChainRepository
            ->fetchIdentityByCredentials($credentials, $this->loginContextService);

        return $this->transformIdentityToUserData($identity);
    }

    public function transformIdentityToUserData(Identity $identity): array
    {
        $loginCredentials = $identity->getLoginCredentials();
        $postalSettings = $identity->getPostalSettings();
        $orderCredentials = $identity->getOrderCredentials();
        $loginContext = $identity->getLoginContext();

        return [
            'password' => $loginCredentials->password,
            'encoder' => $loginCredentials->encoder,
            'active' => $loginCredentials->active ? 1 : 0,
            'firstname' => $postalSettings->firstName,
            'lastname' => $postalSettings->lastName,
            'salutation' => $postalSettings->salutation,
            'title' => $postalSettings->title,
            'customernumber' => $orderCredentials->customerNumber,
            'default_billing_address_id' => $identity->getMainBillingAddress()->id,
            'default_shipping_address_id' => $identity->getMainShippingAddress()->id,
            'subshopID' => $loginContext->subShopId,
            'customergroup' => $loginContext->customerGroupName,
            'paymentID' => $loginContext->paymentId,
            'email' => $identity->getEntity()->email,
            'paymentpreset' => $loginContext->paymentPreset,
        ];
    }

    /**
     * @param string $email
     */
    public function setIdentityFor(string $email)
    {
        try {
            $identity = $this->identityChainRepository
                ->fetchIdentityByEmail($email, $this->loginContextService);
        } catch (NotFoundException $e) {
            return;
        }

        $this->authStorageAdapter
            ->setIdentity($identity);
    }

    /**
     * @param string $email
     * @return Identity
     */
    public function getIdentityByEmail(string $email): Identity
    {
        return $this->identityChainRepository
            ->fetchIdentityByEmail($email, $this->loginContextService);
    }
}
