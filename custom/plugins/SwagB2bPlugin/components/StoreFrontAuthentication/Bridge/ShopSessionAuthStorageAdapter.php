<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Bridge;

use Shopware\B2B\StoreFrontAuthentication\Framework\AuthStorageAdapterInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\NoIdentitySetException;

class ShopSessionAuthStorageAdapter implements AuthStorageAdapterInterface
{
    const IDENTITY_KEY = 'b2b_front_auth_identity';

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $namespace;

    /**
     * @var \sAdmin
     */
    private $sAdmin;

    /**
     * @param \Enlight_Components_Session_Namespace $namespace
     * @param \sAdmin $baseAuth
     */
    public function __construct(\Enlight_Components_Session_Namespace $namespace, \sAdmin $baseAuth)
    {
        $this->namespace = $namespace;
        $this->sAdmin = $baseAuth;
    }

    public function unsetIdentity()
    {
        $this->namespace->offsetUnset(self::IDENTITY_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentity(Identity $identity)
    {
        $this->namespace->offsetSet(self::IDENTITY_KEY, serialize($identity));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(): Identity
    {
        if (!$this->namespace->offsetExists(self::IDENTITY_KEY)) {
            throw new NoIdentitySetException('Session does not have a stored identity');
        }

        $serializedIdentity = $this->namespace->get(self::IDENTITY_KEY);

        return unserialize($serializedIdentity);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated(): bool
    {
        return $this->sAdmin->sCheckUser();
    }
}
