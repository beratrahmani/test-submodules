<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Shopware\B2B\Common\CrudEntity;
use Shopware\B2B\Currency\Framework\CurrencyAware;
use Shopware\B2B\OrderClearance\Framework\OrderItemEntity;

class BudgetEntity extends OrderItemEntity implements CrudEntity, CurrencyAware
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $identifier;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int|null
     */
    public $ownerId = null;

    /**
     * @var bool
     */
    public $notifyAuthor;

    /**
     * @var int
     */
    public $notifyAuthorPercentage;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $refreshType;

    /**
     * @var string
     */
    public $fiscalYear;

    /**
     * @var BudgetStatus
     */
    public $currentStatus;

    /**
     * @var float
     */
    public $currencyFactor = self::DEFAULT_FACTOR;

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return ! (bool) $this->id;
    }

    /**
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'identifier' => $this->identifier,
            'name' => $this->name,
            'owner_id' => $this->ownerId,
            'notify_author' => $this->notifyAuthor,
            'notify_author_percentage' => $this->notifyAuthorPercentage,
            'active' => $this->active,
            'amount' => $this->amount,
            'refresh_type' => $this->refreshType,
            'fiscal_year' => $this->fiscalYear,
            'currency_factor' => $this->currencyFactor,
        ];
    }

    /**
     * @param array $data
     * @return CrudEntity
     */
    public function fromDatabaseArray(array $data): CrudEntity
    {
        $this->id =  (int) $data['id'];
        $this->identifier =  $data['identifier'];
        $this->name =  $data['name'];

        if ($data['owner_id']) {
            $this->ownerId = (int) $data['owner_id'];
        }

        $this->notifyAuthor = (bool) $data['notify_author'];
        $this->notifyAuthorPercentage = (int) $data['notify_author_percentage'];
        $this->active = (bool) $data['active'];
        $this->amount = (float) $data['amount'];
        $this->refreshType = $data['refresh_type'];
        $this->fiscalYear = (string) $data['fiscal_year'];
        $this->setCurrencyFactor((float) $data['currency_factor']);

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

        if ($this->id) {
            $this->id = (int) $this->id;
        }

        if ($this->ownerId) {
            $this->ownerId = (int) $this->ownerId;
        } else {
            $this->ownerId = null;
        }

        if ($this->notifyAuthor) {
            $this->notifyAuthor = (bool) $this->notifyAuthor;
        }

        if ($this->notifyAuthorPercentage) {
            $this->notifyAuthorPercentage = (int) $this->notifyAuthorPercentage;
        }

        if ($this->active) {
            $this->active = (bool) $this->active;
        }

        if ($this->amount) {
            $this->amount = (float) $this->amount;
        }

        if ($this->fiscalYear) {
            $this->fiscalYear = (string) $this->fiscalYear;
        }

        if ($this->currencyFactor) {
            $this->setCurrencyFactor((float) $this->currencyFactor);
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
    public function jsonSerialize()
    {
        return $this->toArray();
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
     * {@inheritdoc}
     */
    public function getAmountPropertyNames(): array
    {
        return [
            'amount',
        ];
    }
}
