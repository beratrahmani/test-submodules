<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework;

class AuditLogAuthorEntity
{
    /**
     * @var string
     */
    public $hash;

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
     * @var bool
     */
    public $isApi;

    /**
     * @var bool
     */
    public $isBackend;

    /**
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'hash' => $this->hash,
            'salutation' => $this->salutation,
            'title' => $this->title,
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'email' => $this->email,
            'is_api' => (int) $this->isApi,
            'is_backend' => (int) $this->isBackend,
        ];
    }

    /**
     * @param array $data
     * @return AuditLogAuthorEntity
     */
    public function fromDatabaseArray(array $data): self
    {
        $this->hash = $data['hash'];
        $this->salutation = $data['salutation'];
        $this->title = $data['title'];
        $this->firstName = $data['firstname'];
        $this->lastName = $data['lastname'];
        $this->email = $data['email'];
        $this->isApi = (bool) $data['is_api'];
        $this->isBackend = (bool) $data['is_backend'];

        return $this;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $properties = array_keys($this->toArray());

        foreach ($data as $key => $value) {
            if (false === in_array($key, $properties, true)) {
                continue;
            }

            $this->{$key} = $value;
        }

        if ($this->isApi) {
            $this->isApi = (bool) $this->isApi;
        }

        if ($this->isBackend) {
            $this->isBackend = (bool) $this->isBackend;
        }
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
