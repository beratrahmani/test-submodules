<?php declare(strict_types=1);

namespace Shopware\B2B\Debtor\Framework;

use Shopware\B2B\Common\Entity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserAddress;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserLoginContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserLoginCredentials;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserOrderCredentials;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserPostalSettings;

class DebtorIdentity implements Identity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var DebtorEntity
     */
    private $debtorCrudEntity;

    /**
     * @var int
     */
    private $authId;

    /**
     * @var string
     */
    private $avatar;

    /**
     * @var bool
     */
    private $isApi;

    /**
     * @param int $authId
     * @param int $id
     * @param string $tableName
     * @param DebtorEntity $debtorCrudEntity
     * @param string $avatar
     * @param bool $isApi
     */
    public function __construct(
        int $authId,
        int $id,
        string $tableName,
        DebtorEntity $debtorCrudEntity,
        string $avatar = '',
        bool $isApi = false
    ) {
        $this->id = $id;
        $this->tableName = $tableName;
        $this->debtorCrudEntity = $debtorCrudEntity;
        $this->authId = $authId;
        $this->avatar = $avatar;
        $this->isApi = $isApi;
    }

    /**
     * @return int
     */
    public function getAuthId(): int
    {
        return $this->authId;
    }

    /**
     * @return int
     */
    public function getContextAuthId(): int
    {
        return $this->authId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->debtorCrudEntity;
    }

    /**
     * @return UserLoginCredentials
     */
    public function getLoginCredentials(): UserLoginCredentials
    {
        $debtorEntity = $this->getDebtorEntity();

        return new UserLoginCredentials(
            $debtorEntity->email,
            $debtorEntity->password,
            $debtorEntity->encoder,
            $debtorEntity->active
        );
    }

    /**
     * @return UserPostalSettings
     */
    public function getPostalSettings(): UserPostalSettings
    {
        $debtorEntity = $this->getDebtorEntity();

        return new UserPostalSettings(
            $debtorEntity->salutation,
            $debtorEntity->title,
            $debtorEntity->firstName,
            $debtorEntity->lastName,
            $debtorEntity->language,
            $debtorEntity->email
        );
    }

    /**
     * @return UserOrderCredentials
     */
    public function getOrderCredentials(): UserOrderCredentials
    {
        $debtorEntity = $this->getDebtorEntity();

        return new UserOrderCredentials(
            $debtorEntity->customernumber,
            $debtorEntity->id,
            $debtorEntity->email
        );
    }

    /**
     * @return UserLoginContext
     */
    public function getLoginContext(): UserLoginContext
    {
        return new UserLoginContext(
            $this->getDebtorEntity()->subshopID,
            $this->getDebtorEntity()->customergroup,
            $this->getDebtorEntity()->paymentID,
            (string) $this->avatar,
            $this->getDebtorEntity()->paymentpreset
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMainShippingAddress(): UserAddress
    {
        return new UserAddress($this->getDebtorEntity()->default_shipping_address_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getMainBillingAddress(): UserAddress
    {
        return new UserAddress($this->getDebtorEntity()->default_billing_address_id);
    }

    /**
     * @return DebtorEntity
     */
    private function getDebtorEntity(): DebtorEntity
    {
        return $this->getEntity();
    }

    /**
     * @return OwnershipContext
     */
    public function getOwnershipContext(): OwnershipContext
    {
        return new OwnershipContext(
            $this->authId,
            $this->authId,
            $this->getDebtorEntity()->email,
            $this->getDebtorEntity()->id,
            $this->getDebtorEntity()->id,
            __CLASS__
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isSuperAdmin(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isApiUser(): bool
    {
        return $this->isApi ? true : false;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar)
    {
        $this->avatar = $avatar;
    }
}
