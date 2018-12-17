<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use DateTime;
use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\Common\Repository\MysqlRepository;
use Shopware\B2B\Currency\Framework\CurrencyAware;

class OfferEntity implements CrudEntity, CurrencyAware
{
    const DISCOUNT_REFERENCE = 'B2BDISCOUNT';
    const STATE_OPEN = 'offer_status_open';
    const STATE_ACCEPTED_USER = 'offer_status_accepted_user';
    const STATE_ACCEPTED_ADMIN = 'offer_status_accepted_admin';
    const STATE_DECLINED_USER = 'offer_status_declined_user';
    const STATE_DECLINED_ADMIN = 'offer_status_declined_admin';
    const STATE_EXPIRED = 'offer_status_expired';
    const STATE_ACCEPTED_OF_BOTH = 'offer_status_accepted_both';
    const STATE_CONVERTED = 'offer_status_converted';

    /**
     * @var int
     */
    public $id;

    /**
     * @var float
     */
    public $currencyFactor;

    /**
     * @var float
     */
    public $discountAmount;

    /**
     * @var float
     */
    public $discountAmountNet;

    /**
     * @var int
     */
    public $orderContextId;

    /**
     * @var int
     */
    public $authId;

    /**
     * @var float
     */
    public $discountValueNet;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $debtorEmail;

    /**
     * @var int
     */
    public $listId;

    /**
     * @var DateTime readOnly
     */
    public $createdAt;

    /**
     * @var DateTime readOnly
     */
    public $expiredAt;

    /**
     * @var DateTime readOnly
     */
    public $changedByUserAt;

    /**
     * @var DateTime readOnly
     */
    public $changedByAdminAt;

    /**
     * @var DateTime readOnly
     */
    public $acceptedByUserAt;

    /**
     * @var DateTime readOnly
     */
    public $acceptedByAdminAt;

    /**
     * @var DateTime readOnly
     */
    public $declinedByUserAt;

    /**
     * @var DateTime readOnly
     */
    public $declinedByAdminAt;

    /**
     * @var DateTime readOnly
     */
    public $convertedAt;

    /**
     * @var DateTime readOnly
     */
    public $changedStatusAt;

    /**
     * @var string readOnly
     */
    public $status;

    /**
     * @var array
     */
    private $datesToUpdate = [];

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
        $databaseArray = [
            'id' => $this->id,
            'discount_amount' => $this->discountAmount,
            'discount_amount_net' => $this->discountAmountNet,
            'discount_value_net' => $this->discountValueNet,
            'auth_id' => $this->authId,
            'order_context_id' => $this->orderContextId,
            'currency_factor' => $this->currencyFactor,
            'list_id' => $this->listId,
        ];

        if (isset($this->email)) {
            $databaseArray['email'] = $this->email;
        }

        if (isset($this->debtorEmail)) {
            $databaseArray['debtor_email'] = $this->debtorEmail;
        }

        return $databaseArray;
    }

    /**
     * @return array
     */
    public function datesToDatabaseArray(): array
    {
        $this->changedStatusAt = (new \DateTime())->format(MysqlRepository::MYSQL_DATETIME_FORMAT);

        $databaseArray = array_merge($this->datesToUpdate, [
            'id' => $this->id,
            'changed_status_at' => $this->changedStatusAt,
        ]);

        $this->datesToUpdate = [];

        return $databaseArray;
    }

    /**
     * @param \DateTime[] $dates
     */
    public function setDates(array $dates)
    {
        foreach ($dates as $property => $date) {
            if (!($date instanceof \DateTime)) {
                continue;
            }

            try {
                $databaseKey = $this->getDateDatabaseField($property);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $this->$property = $date;
            $this->datesToUpdate[$databaseKey] = $date->format(MysqlRepository::MYSQL_DATETIME_FORMAT);
        }
    }

    /**
     * @param string[] $dates
     */
    public function updateDates(array $dates)
    {
        foreach ($dates as $property) {
            try {
                $databaseKey = $this->getDateDatabaseField($property);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $this->$property = $date = new \DateTime();
            $this->datesToUpdate[$databaseKey] = $date->format(MysqlRepository::MYSQL_DATETIME_FORMAT);
        }
    }

    /**
     * @param string[] $dates
     */
    public function removeDates(array $dates)
    {
        foreach ($dates as $property) {
            try {
                $databaseKey = $this->getDateDatabaseField($property);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $this->$property = null;
            $this->datesToUpdate[$databaseKey] = null;
        }
    }

    /**
     * @param string $propertyName
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getDateDatabaseField(string $propertyName): string
    {
        switch ($propertyName) {
            case 'changedByUserAt': return 'changed_user_at';
            case 'changedByAdminAt': return 'changed_admin_at';
            case 'createdAt': return 'created_at';
            case 'expiredAt': return 'expired_at';
            case 'acceptedByUserAt': return 'accepted_user_at';
            case 'acceptedByAdminAt': return 'accepted_admin_at';
            case 'declinedByAdminAt': return 'declined_admin_at';
            case 'declinedByUserAt': return 'declined_user_at';
            case 'convertedAt': return 'converted_at';
        }

        throw new \InvalidArgumentException('no date field property given');
    }

    /**
     * {@inheritdoc}
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id = (int) $data['id'];
        $this->createdAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['created_at']);
        $this->discountAmount = (float) $data['discount_amount'];
        $this->discountAmountNet = (float) $data['discount_amount_net'];
        $this->discountValueNet = (float) $data['discount_value_net'];
        $this->authId = (int) $data['auth_id'];
        $this->orderContextId = (int) $data['order_context_id'];
        $this->setCurrencyFactor((float) $data['currency_factor']);
        $this->listId = (int) $data['list_id'];
        $this->email = $data['email'];
        $this->debtorEmail = $data['debtor_email'];

        if (isset($data['changed_user_at'])) {
            $this->changedByUserAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['changed_user_at']);
        }

        if (isset($data['changed_admin_at'])) {
            $this->changedByAdminAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['changed_admin_at']);
        }

        if (isset($data['changed_status_at'])) {
            $this->changedStatusAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['changed_status_at']);
        }

        if (isset($data['expired_at'])) {
            $this->expiredAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['expired_at']);
        }

        if (isset($data['accepted_user_at'])) {
            $this->acceptedByUserAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['accepted_user_at']);
        }

        if (isset($data['accepted_admin_at'])) {
            $this->acceptedByAdminAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['accepted_admin_at']);
        }

        if (isset($data['declined_admin_at'])) {
            $this->declinedByAdminAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['declined_admin_at']);
        }

        if (isset($data['declined_user_at'])) {
            $this->declinedByUserAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['declined_user_at']);
        }

        if (isset($data['converted_at'])) {
            $this->convertedAt = DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['converted_at']);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEditableByUser(): bool
    {
        return $this->status === 'offer_status_open' || $this->status === 'offer_status_declined_user';
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        $data = $this->toArray();
        unset($data['datesToUpdate']);

        return $data;
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
    public function getCurrencyFactor(): float
    {
        return $this->currencyFactor;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyFactor(float $factor)
    {
        $this->currencyFactor = $factor;
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
    public function getAmountPropertyNames(): array
    {
        return [
            'discountAmount',
            'discountAmountNet',
            'discountValueNet',
        ];
    }
}
