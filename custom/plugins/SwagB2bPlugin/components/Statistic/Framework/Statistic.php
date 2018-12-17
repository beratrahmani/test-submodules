<?php declare(strict_types=1);

namespace Shopware\B2B\Statistic\Framework;

use Shopware\B2B\Common\Repository\MysqlRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\UserPostalSettings;

class Statistic
{
    /**
     * @var \DateTime
     */
    public $createdAt;

    /**
     * @var \DateTime
     */
    public $clearedAt;

    /**
     * @var UserPostalSettings|null
     */
    public $contact = null;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var float
     */
    public $amountNet;

    /**
     * @var string
     */
    public $status;

    /**
     * @var int
     */
    public $itemCount;

    /**
     * @var int
     */
    public $itemQuantityCount;

    /**
     * @var string
     */
    public $orderNumber;

    /**
     * @var int
     */
    public $orderContextId;

    /**
     * @var int
     */
    public $listId;

    /**
     * @param string[] $data
     * @return Statistic
     */
    public function fromDatabaseArray(array $data): self
    {
        $this->createdAt = \DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['created_at']);
        if ($data['cleared_at']) {
            $this->clearedAt = \DateTime::createFromFormat(MysqlRepository::MYSQL_DATETIME_FORMAT, $data['cleared_at']);
        }
        $this->amount = (float) $data['amount'];
        $this->amountNet = (float) $data['amount_net'];
        $this->itemCount = (int) $data['itemCount'];
        $this->itemQuantityCount = (int) $data['itemQuantityCount'];
        $this->orderNumber = $data['ordernumber'];
        $this->orderContextId = (int) $data['orderContextId'];
        $this->listId = (int) $data['listId'];
        $this->status = $data['status'];
        if (array_key_exists('contact', $data)) {
            $this->contact = $data['contact'];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
