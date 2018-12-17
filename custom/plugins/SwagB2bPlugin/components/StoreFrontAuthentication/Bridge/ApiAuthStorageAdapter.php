<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Bridge;

use Shopware\B2B\StoreFrontAuthentication\Framework\AuthStorageAdapterInterface;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\NoIdentitySetException;

class ApiAuthStorageAdapter implements AuthStorageAdapterInterface
{
    /**
     * @var Identity
     */
    private $identity;

    public function unsetIdentity()
    {
        $this->identity = null;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentity(Identity $identity)
    {
        $this->identity = $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(): Identity
    {
        if (!$this->identity) {
            throw new NoIdentitySetException();
        }

        return $this->identity;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated(): bool
    {
        return $this->identity ? true : false;
    }
}
