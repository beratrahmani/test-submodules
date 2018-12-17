<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

interface AuthStorageAdapterInterface
{
    public function unsetIdentity();

    /**
     * @param Identity $identity
     * @return mixed
     */
    public function setIdentity(Identity $identity);

    /**
     * @return Identity
     */
    public function getIdentity(): Identity;

    /**
     * @return bool
     */
    public function isAuthenticated(): bool;
}
