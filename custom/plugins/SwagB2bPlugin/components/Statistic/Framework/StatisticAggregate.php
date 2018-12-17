<?php declare(strict_types=1);

namespace Shopware\B2B\Statistic\Framework;

class StatisticAggregate
{
    /**
     * @var int
     */
    public $createdAtGrouping;

    /**
     * @var int
     */
    public $createdAtYear;

    /**
     * @var float
     */
    public $orderAmount;

    /**
     * @var float
     */
    public $orderAmountNet;

    /**
     * @var int
     */
    public $orders;

    /**
     * @var int
     */
    public $itemCount;

    /**
     * @var int
     */
    public $itemQuantityCount;

    /**
     * @param array $data
     * @return StatisticAggregate
     */
    public function fromDatabaseArray(array $data): self
    {
        $this->createdAtGrouping = $data['createdAtGrouping'];
        $this->createdAtYear = $data['createdAtYear'];
        $this->orderAmount = $data['orderAmount'];
        $this->orderAmountNet = $data['orderAmountNet'];
        $this->orders = $data['orders'];
        $this->itemCount = $data['itemCount'];
        $this->itemQuantityCount = $data['itemQuantityCount'];

        return $this;
    }
}
