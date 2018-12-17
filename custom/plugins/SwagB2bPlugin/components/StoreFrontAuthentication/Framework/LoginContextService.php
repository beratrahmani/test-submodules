<?php declare(strict_types = 1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

class LoginContextService
{
    /**
     * @var StoreFrontAuthenticationRepository
     */
    private $authenticationRepository;

    /**
     * @param StoreFrontAuthenticationRepository $authenticationRepository
     */
    public function __construct(StoreFrontAuthenticationRepository $authenticationRepository)
    {
        $this->authenticationRepository = $authenticationRepository;
    }

    /**
     * @param string $providerClass
     * @param int $providerContext
     * @param int $contextOwnerId
     * @return int
     */
    public function getAuthId(string $providerClass, int $providerContext, int $contextOwnerId = null): int
    {
        $authId = $this->authenticationRepository->fetchIdByProviderData($providerClass, $providerContext);

        if (!$authId) {
            $authId = $this->authenticationRepository
                ->createAuthContextEntry($providerClass, $providerContext, $contextOwnerId);
        }

        return $authId;
    }

    /**
     * @param int $authId
     * @return string
     */
    public function getAvatar(int $authId): string
    {
        return $this->authenticationRepository->fetchAvatarById($authId);
    }
}
