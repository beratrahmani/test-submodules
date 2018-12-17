<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

class AuthenticationService
{
    /**
     * @var AuthStorageAdapterInterface
     */
    private $authStorageAdapter;

    /**
     * @var StoreFrontAuthenticationRepository
     */
    private $authenticationRepository;

    /**
     * @var IdentityChainIdentityLoader
     */
    private $identityChainRepository;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var bool
     */
    private $isLoggedIn;

    /**
     * @param AuthStorageAdapterInterface $authStorageAdapter
     * @param StoreFrontAuthenticationRepository $authenticationRepository
     * @param IdentityChainIdentityLoader $identityChainRepository
     * @param LoginContextService $loginContextService
     */
    public function __construct(
        AuthStorageAdapterInterface $authStorageAdapter,
        StoreFrontAuthenticationRepository $authenticationRepository,
        IdentityChainIdentityLoader $identityChainRepository,
        LoginContextService $loginContextService
    ) {
        $this->authStorageAdapter = $authStorageAdapter;
        $this->authenticationRepository = $authenticationRepository;
        $this->identityChainRepository = $identityChainRepository;
        $this->loginContextService = $loginContextService;
    }

    /**
     * @return Identity
     */
    public function getIdentity(): Identity
    {
        if (!$this->isAuthenticated()) {
            throw new NotAuthenticatedException('Not authenticated, can not provide a valid identity.');
        }

        return $this->authStorageAdapter->getIdentity();
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        if ($this->isLoggedIn) {
            return $this->isLoggedIn;
        }

        return $this->isLoggedIn = $this->authStorageAdapter->isAuthenticated();
    }

    /**
     * @return bool
     */
    public function isB2b(): bool
    {
        try {
            $this->getIdentity();
        } catch (NotAuthenticatedException $e) {
            return false;
        } catch (NoIdentitySetException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $className
     * @return bool
     */
    public function is($className): bool
    {
        if (!$this->isB2b()) {
            return false;
        }

        return $this->getIdentity() instanceof $className;
    }

    /**
     * @param int $authId
     * @return Identity
     */
    public function getIdentityByAuthId(int $authId): Identity
    {
        $auth = $this->authenticationRepository
            ->fetchAuthenticationById($authId);

        return $this->identityChainRepository
            ->fetchIdentityByAuthentication($auth, $this->loginContextService);
    }
}
