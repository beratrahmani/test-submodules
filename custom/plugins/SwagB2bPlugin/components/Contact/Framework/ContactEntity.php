<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\Debtor\Framework\DebtorEntity;

class ContactEntity implements CrudEntity
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
    public $language;

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
     * @var
     */
    public $department;

    /**
     * @var int
     */
    public $contextOwnerId;

    /**
     * @var null|DebtorEntity
     */
    public $debtor;

    /**
     * Read Only
     *
     * @var int
     */
    public $authId;

    /**
     * read only
     * @var int
     */
    public $defaultBillingAddressId;

    /**
     * read only
     * @var int
     */
    public $defaultShippingAddressId;

    /**
     * {@inheritdoc}
     */
    public function isNew(): bool
    {
        return ! (bool) $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'password' => $this->password,
            'encoder' => $this->encoder,
            'email' => $this->email,
            'active' => $this->active ? 1 : 0,
            'language' => $this->language,
            'title' => $this->title,
            'salutation' => $this->salutation,
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'department' => $this->department,
            'context_owner_id' => $this->contextOwnerId,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->password = $data['password'];
        $this->encoder = $data['encoder'];
        $this->email = $data['email'];
        $this->active = (bool) $data['active'];
        $this->language = (int) $data['language'];
        $this->title = (string) $data['title'];
        $this->salutation = (string) $data['salutation'];
        $this->firstName = $data['firstname'];
        $this->lastName = $data['lastname'];
        $this->department = $data['department'];
        $this->contextOwnerId = (int) $data['context_owner_id'];
        $this->authId = (int) $data['auth_id'];

        if (isset($data['default_billing_address_id'])) {
            $this->defaultBillingAddressId = (int) $data['default_billing_address_id'];
        }

        if (isset($data['default_shipping_address_id'])) {
            $this->defaultShippingAddressId = (int) $data['default_shipping_address_id'];
        }

        return $this;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }

        $this->active = (bool) $this->active;

        if ($this->id) {
            $this->id = (int) $this->id;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $contactArray = get_object_vars($this);

        unset($contactArray['debtor']);

        return $contactArray;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
