<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

use Shopware\B2B\Common\Entity;

interface Identity
{
    /**
     * @return int
     */
    public function getAuthId(): int;

    /**
     * @return int
     */
    public function getContextAuthId(): int;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getTableName(): string;

    /**
     * @return OwnershipContext
     */
    public function getOwnershipContext(): OwnershipContext;

    /**
     * @return UserLoginCredentials
     */
    public function getLoginCredentials(): UserLoginCredentials;

    /**
     * @return UserLoginContext
     */
    public function getLoginContext(): UserLoginContext;

    /**
     * @return UserPostalSettings
     */
    public function getPostalSettings(): UserPostalSettings;

    /**
     * @return UserOrderCredentials
     */
    public function getOrderCredentials(): UserOrderCredentials;

    /**
     * @return UserAddress
     */
    public function getMainShippingAddress(): UserAddress;

    /**
     * @return UserAddress
     */
    public function getMainBillingAddress(): UserAddress;

    /**
     * @return Entity
     */
    public function getEntity(): Entity;

    /**
     * @return bool
     */
    public function isSuperAdmin(): bool;

    /**
     * @return bool
     */
    public function isApiUser(): bool;

    /**
     * @return string
     */
    public function getAvatar(): string;

    /**
     * @param string $avatar
     */
    public function setAvatar(string $avatar);
}
