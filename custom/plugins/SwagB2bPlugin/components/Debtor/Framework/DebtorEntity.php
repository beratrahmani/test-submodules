<?php declare(strict_types=1);

namespace Shopware\B2B\Debtor\Framework;

use Shopware\B2B\Common\Entity;

class DebtorEntity implements Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $encoder;

    /**
     * @var string
     */
    public $email;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var int
     */
    public $accountmode;

    /**
     * @var string
     */
    public $confirmationkey;

    /**
     * @var int
     */
    public $paymentID;

    /**
     * @var int
     */
    public $firstlogin;


    /**
     * @var int
     */
    public $lastlogin;

    /**
     * @var int
     */
    public $sessionID;

    /**
     * @var int
     */
    public $newsletter;

    /**
     * @var int
     */
    public $validation;

    /**
     * @var int
     */
    public $affiliate;

    /**
     * @var string
     */
    public $customergroup;

    /**
     * @var int
     */
    public $paymentpreset;

    /**
     * @var int
     */
    public $language;

    /**
     * @var int
     */
    public $subshopID;

    /**
     * @var int
     */
    public $referer;

    /**
     * @var int
     */
    public $pricegroupID;

    /**
     * @var int
     */
    public $internalcomment;

    /**
     * @var int
     */
    public $failedlogins;

    /**
     * @var int
     */
    public $lockeduntil;

    /**
     * @var int
     */
    public $default_billing_address_id;

    /**
     * @var int
     */
    public $default_shipping_address_id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $salutation;

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
    public $birthday;

    /**
     * @var string
     */
    public $customernumber;

    /**
     * @param array $contactData
     * @return Entity
     */
    public function fromDatabaseArray(array $contactData): Entity
    {
        $this->id = (int) $contactData['id'];
        $this->password = $contactData['password'];
        $this->encoder = $contactData['encoder'];
        $this->email = $contactData['email'];
        $this->active = (bool) $contactData['active'];
        $this->accountmode = $contactData['accountmode'];
        $this->confirmationkey = $contactData['confirmationkey'];
        $this->paymentID = (int) $contactData['paymentID'];
        $this->firstlogin = $contactData['firstlogin'];
        $this->lastlogin = $contactData['lastlogin'];
        $this->sessionID = $contactData['sessionID'];
        $this->newsletter = $contactData['newsletter'];
        $this->validation = $contactData['validation'];
        $this->affiliate = $contactData['affiliate'];
        $this->customergroup = $contactData['customergroup'];
        $this->paymentpreset = (int) $contactData['paymentpreset'];
        $this->language = (int) $contactData['language'];
        $this->subshopID = (int) $contactData['subshopID'];
        $this->referer = $contactData['referer'];
        $this->pricegroupID = $contactData['pricegroupID'];
        $this->internalcomment = $contactData['internalcomment'];
        $this->failedlogins = $contactData['failedlogins'];
        $this->lockeduntil = $contactData['lockeduntil'];
        $this->default_billing_address_id = (int) $contactData['default_billing_address_id'];
        $this->default_shipping_address_id = (int) $contactData['default_shipping_address_id'];
        $this->title = (string) $contactData['title'];
        $this->salutation = (string) $contactData['salutation'];
        $this->firstName = (string) $contactData['firstname'];
        $this->lastName = (string) $contactData['lastname'];
        $this->birthday = $contactData['birthday'];
        $this->customernumber = (string) $contactData['customernumber'];

        return $this;
    }
}
