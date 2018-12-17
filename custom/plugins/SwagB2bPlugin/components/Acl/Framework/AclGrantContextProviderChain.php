<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AclGrantContextProviderChain implements AclGrantContextProvider
{
    /**
     * @var AclGrantContextProvider[]
     */
    private $provider;

    /**
     * @param AclGrantContextProvider ...$provider
     */
    public function __construct(AclGrantContextProvider ...$provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     * @throws AclUnsupportedIdentifierException
     */
    public function fetchOneByIdentifier(string $identifier, OwnershipContext $ownershipContext): AclGrantContext
    {
        foreach ($this->provider as $provider) {
            try {
                return $provider->fetchOneByIdentifier($identifier, $ownershipContext);
            } catch (AclUnsupportedIdentifierException $e) {
                //nth
            }
        }

        throw new AclUnsupportedIdentifierException(
            sprintf('Unable to load AclGrantContext by identifier %s', $identifier)
        );
    }
}
