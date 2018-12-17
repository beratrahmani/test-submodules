<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework;

use Shopware\B2B\Address\Framework\AddressEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class SalesRepresentativeClientEntity
{
    /**
     * @var int
     */
    public $authId;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var bool
     */
    public $active;

    /**
     * @param Identity $identity
     * @param AddressEntity $address
     */
    public function __construct(Identity $identity, AddressEntity $address)
    {
        $postal = $identity->getPostalSettings();

        $this->authId = $identity->getAuthId();
        $this->firstName = $postal->firstName;
        $this->lastName = $postal->lastName;
        $this->email = $postal->email;
        $this->active = $identity->getLoginCredentials()->active;
        $this->phone = $address->phone;
    }
}
