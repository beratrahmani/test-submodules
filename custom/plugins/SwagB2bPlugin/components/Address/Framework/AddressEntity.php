<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

use Shopware\B2B\Common\CrudEntity;

/**
 * Represents an address. As an entity this is used in CRUD & listing operations.
 */
class AddressEntity implements CrudEntity
{
    const TYPE_BILLING = 'billing';
    const TYPE_SHIPPING = 'shipping';

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var string
     */
    public $company;

    /**
     * @var string
     */
    public $department;

    /**
     * @var string
     */
    public $salutation;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $street;

    /**
     * @var string
     */
    public $zipcode;

    /**
     * @var string
     */
    public $city;

    /**
     * @var int
     */
    public $country_id;

    /**
     * @var int
     */
    public $state_id;

    /**
     * @var string
     */
    public $ustid;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $additional_address_line1;

    /**
     * @var string
     */
    public $additional_address_line2;

    /**
     * @var bool
     */
    public $is_used = false;

    /**
     * @var string
     */
    public $type;

    /**
     * {@inheritdoc}
     */
    public function isNew(): bool
    {
        return !(bool) $this->id;
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
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => (int) $this->id,
            'user_id' => $this->user_id,
            'company' => $this->company,
            'department' => $this->department,
            'salutation' => $this->salutation,
            'title' => $this->title,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'street' => $this->street,
            'zipcode' => $this->zipcode,
            'city' => $this->city,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'ustid' => $this->ustid,
            'phone' => $this->phone,
            'additional_address_line1' => $this->additional_address_line1,
            'additional_address_line2' => $this->additional_address_line2,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->user_id = $data['user_id'];
        $this->company = $data['company'];
        $this->department = $data['department'];
        $this->salutation = $data['salutation'];
        $this->title = $data['title'];
        $this->firstname = $data['firstname'];
        $this->lastname = $data['lastname'];
        $this->street = $data['street'];
        $this->zipcode = $data['zipcode'];
        $this->city = $data['city'];
        $this->country_id = (int) $data['country_id'];
        $this->state_id = $data['state_id'];
        $this->ustid = $data['ustid'];
        $this->phone = $data['phone'];
        $this->additional_address_line1 = $data['additional_address_line1'];
        $this->additional_address_line2 = $data['additional_address_line2'];
        $this->is_used =  isset($data['is_used']) ? (bool) $data['is_used'] : false;
        $this->type =  isset($data['type']) ? $data['type'] : null;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
