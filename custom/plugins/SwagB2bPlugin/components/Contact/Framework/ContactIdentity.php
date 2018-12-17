<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Shopware\B2B\Common\Entity;
use Shopware\B2B\Debtor\Framework\DebtorEntity;
use Shopware\B2B\Debtor\Framework\DebtorIdentity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserAddress;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserLoginContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserLoginCredentials;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserOrderCredentials;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserPostalSettings;

class ContactIdentity implements Identity
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
     * @var ContactEntity
     */
    private $contact;

    /**
     * @var int
     */
    private $authId;

    /**
     * @var DebtorIdentity
     */
    private $debtorIdentity;

    /**
     * @var string
     */
    private $avatar;

    /**
     * @param int $authId
     * @param int $id
     * @param string $tableName
     * @param ContactEntity $contact
     * @param DebtorIdentity $debtorIdentity
     * @param string $avatar
     */
    public function __construct(
        int $authId,
        int $id,
        string $tableName,
        ContactEntity $contact,
        DebtorIdentity $debtorIdentity,
        string $avatar = ''
    ) {
        $this->authId = $authId;
        $this->id = $id;
        $this->tableName = $tableName;
        $this->contact = $contact;
        $this->debtorIdentity = $debtorIdentity;
        $this->avatar = $avatar;
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
        return $this->debtorIdentity->getAuthId();
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
        return $this->contact;
    }

    /**
     * @return UserLoginCredentials
     */
    public function getLoginCredentials(): UserLoginCredentials
    {
        $contactEntity = $this->getContactEntity();

        return new UserLoginCredentials(
            $contactEntity->email,
            $contactEntity->password,
            $contactEntity->encoder,
            $contactEntity->active
        );
    }

    /**
     * @return UserPostalSettings
     */
    public function getPostalSettings(): UserPostalSettings
    {
        $contactEntity = $this->getContactEntity();

        return new UserPostalSettings(
            $contactEntity->salutation,
            $contactEntity->title,
            $contactEntity->firstName,
            $contactEntity->lastName,
            $this->getDebtorEntity()->language,
            $contactEntity->email
        );
    }

    /**
     * @return UserOrderCredentials
     */
    public function getOrderCredentials(): UserOrderCredentials
    {
        $debtorEntity = $this->getDebtorEntity();
        $contactEntity = $this->getContactEntity();

        return new UserOrderCredentials(
            $debtorEntity->customernumber,
            $debtorEntity->id,
            $contactEntity->email
        );
    }

    /**
     * @return UserAddress
     */
    public function getMainShippingAddress(): UserAddress
    {
        if ($this->getContactEntity()->defaultShippingAddressId) {
            return new UserAddress($this->getContactEntity()->defaultShippingAddressId);
        }

        return new UserAddress($this->getDebtorEntity()->default_shipping_address_id);
    }

    /**
     * @return UserAddress
     */
    public function getMainBillingAddress(): UserAddress
    {
        if ($this->getContactEntity()->defaultBillingAddressId) {
            return new UserAddress($this->getContactEntity()->defaultBillingAddressId);
        }

        return new UserAddress($this->getDebtorEntity()->default_billing_address_id);
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
            $this->avatar,
            $this->getDebtorEntity()->paymentpreset
        );
    }

    /**
     * @return DebtorEntity
     */
    private function getDebtorEntity(): DebtorEntity
    {
        return $this->debtorIdentity->getEntity();
    }

    /**
     * @return ContactEntity
     */
    private function getContactEntity(): ContactEntity
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
            $this->debtorIdentity->getAuthId(),
            $this->getDebtorEntity()->email,
            (int) $this->debtorIdentity->getId(),
            $this->getId(),
            __CLASS__
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isSuperAdmin(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isApiUser(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar(string $avatar)
    {
        $this->avatar = $avatar;
    }
}
