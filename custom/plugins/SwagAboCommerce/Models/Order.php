<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagAboCommerce\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_plugin_swag_abo_commerce_orders")
 */
class Order extends ModelEntity
{
    const UNIT_MONTH = 'months';
    const UNIT_WEEK = 'weeks';

    /**
     * @var array
     */
    private $validUnits = [
        self::UNIT_MONTH,
        self::UNIT_WEEK,
    ];

    /**
     * Unique identifier
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The id of the selected customer.
     *
     * @var int
     * @ORM\Column(name="customer_id", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var \Shopware\Models\Customer\Customer
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @var \Shopware\Models\Order\Order
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;

    /**
     * The id of the selected order.
     *
     * @var int
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var \Shopware\Models\Order\Detail
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Detail")
     * @ORM\JoinColumn(name="article_order_detail_id", referencedColumnName="id")
     */
    private $articleOrderDetail;

    /**
     * The id of the selected order.
     *
     * @var int
     * @ORM\Column(name="article_order_detail_id", type="integer", nullable=true)
     */
    private $articleOrderDetailId;

    /**
     * @var \Shopware\Models\Order\Detail
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Detail")
     * @ORM\JoinColumn(name="discount_order_detail_id", referencedColumnName="id")
     */
    private $discountOrderDetail;

    /**
     * The id of the selected order.
     *
     * @var int
     * @ORM\Column(name="discount_order_detail_id", type="integer", nullable=true)
     */
    private $discountOrderDetailId;

    /**
     * @var \Shopware\Models\Order\Order
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Order")
     * @ORM\JoinColumn(name="last_order_id", referencedColumnName="id")
     */
    private $lastOrder;

    /**
     * The id of the last abo order
     *
     * @var int
     * @ORM\Column(name="last_order_id", type="integer", nullable=true)
     */
    private $lastOrderId;

    /**
     * Duration in unit $durationUnit
     *
     * @var int
     * @ORM\Column(name="duration", type="integer", nullable=true)
     */
    private $duration;

    /**
     * Unit of $duration weeks/months etc.
     *
     * @var string
     * @ORM\Column(name="duration_unit", type="string", nullable=true)
     */
    private $durationUnit;

    /**
     * Maximum duration in unit $maxDurationUnit
     *
     * @var int
     * @ORM\Column(name="delivery_interval", type="integer", nullable=false)
     */
    private $deliveryInterval;

    /**
     * Unit of $deliveryInterval weeks/months etc.
     *
     * @var string
     * @ORM\Column(name="delivery_interval_unit", type="string", nullable=false)
     */
    private $deliveryIntervalUnit;

    /**
     * Determines if ordered subscription has no fixed runtime but is endless
     *
     * @var bool
     *
     * @ORM\Column(name="endless_subscription", type="boolean", nullable=false)
     */
    private $endlessSubscription;

    /**
     * @var string
     *
     * @ORM\Column(name="period_of_notice_interval", type="integer", nullable=true)
     */
    private $periodOfNoticeInterval;

    /**
     * @var int
     *
     * @ORM\Column(name="period_of_notice_unit", type="string", nullable=true)
     */
    private $periodOfNoticeUnit;

    /**
     * @var bool
     *
     * @ORM\Column(name="direct_termination", type="boolean")
     */
    private $directTermination;

    /**
     * Datetime of creation
     *
     * @var \DateTime
     * @ORM\Column(name="termination_date", type="datetime")
     */
    private $terminationDate;

    /**
     * Current Date - used for unit testing
     *
     * @var \DateTime
     */
    private $dateNow;

    /**
     * Datetime of creation
     *
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * Due Date of next Order
     *
     * @var \DateTime
     * @ORM\Column(name="due_date", type="date")
     */
    private $dueDate;

    /**
     * Datetime of latest successful run
     *
     * @var \DateTime
     * @ORM\Column(name="recent_run", type="datetime")
     */
    private $recentRun;

    /**
     * Datetime of last run
     *
     * @var \DateTime
     * @ORM\Column(name="last_run", type="date")
     */
    private $lastRun;

    /**
     * @var int
     * @ORM\Column(name="delivered", type="integer", nullable=true)
     */
    private $delivered;

    /**
     * @var int|null
     * @ORM\Column(name="payment_id", type="integer", nullable=true)
     */
    private $paymentId;

    /**
     * @var int|null
     * @ORM\Column(name="billing_address_id", type="integer", nullable=true)
     */
    private $billingAddressId;

    /**
     * @var int|null
     * @ORM\Column(name="shipping_address_id", type="integer", nullable=true)
     */
    private $shippingAddressId;

    /**
     * @param int      $customerId
     * @param int      $orderId
     * @param int      $productOrderDetailId
     * @param int|null $discountOrderDetailId
     * @param int      $duration
     * @param string   $durationUnit
     * @param int      $deliveryInterval
     * @param string   $deliveryIntervalUnit
     * @param bool     $endlessSubscription
     * @param int|null $periodOfNoticeInterval
     * @param string   $periodOfNoticeUnit
     * @param bool     $directTermination
     * @param int|null $paymentId
     * @param int|null $billingAddressId
     * @param int|null $shippingAddressId
     */
    public function __construct(
        $customerId,
        $orderId,
        $productOrderDetailId,
        $discountOrderDetailId = null,
        $duration = null,
        $durationUnit = null,
        $deliveryInterval,
        $deliveryIntervalUnit,
        $endlessSubscription = false,
        $periodOfNoticeInterval = null,
        $periodOfNoticeUnit = null,
        $directTermination = false,
        $paymentId = null,
        $billingAddressId = null,
        $shippingAddressId = null
    ) {
        $this->setCustomerId($customerId);
        $this->setOrderId($orderId);
        $this->setLastOrderId($orderId);
        $this->setArticleOrderDetailId($productOrderDetailId);

        //Will be set to one, because the order itself was delivered already.
        //If we have a duration of 12 weeks there will be 13 deliveries!
        $this->setDelivered(1);
        $this->setRecentRun();

        if ($discountOrderDetailId !== null) {
            $this->setDiscountOrderDetailId($discountOrderDetailId);
        }

        $this->setDuration($duration);
        $this->setDurationUnit($durationUnit);

        $this->setDeliveryInterval($deliveryInterval);
        $this->setDeliveryIntervalUnit($deliveryIntervalUnit);

        $this->setEndlessSubscription($endlessSubscription);
        $this->setPeriodOfNoticeInterval($periodOfNoticeInterval);
        $this->setPeriodOfNoticeUnit($periodOfNoticeUnit);
        $this->setDirectTermination($directTermination);

        $this->setCreated();
        $this->incrementDueDate();

        if (!$endlessSubscription) {
            $this->calculateLastRunDate();
        }

        $this->setPaymentId($paymentId);
        $this->setBillingAddressId($billingAddressId);
        $this->setShippingAddressId($shippingAddressId);
    }

    /**
     * @return \Shopware\Models\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return \Shopware\Models\Order\Detail
     */
    public function getArticleOrderDetail()
    {
        return $this->articleOrderDetail;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopware\Models\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param int $customerId
     *
     * @return Order
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $productOrderDetailId
     *
     * @return Order
     */
    public function setArticleOrderDetailId($productOrderDetailId)
    {
        $this->articleOrderDetailId = $productOrderDetailId;

        return $this;
    }

    /**
     * @return int
     */
    public function getArticleOrderDetailId()
    {
        return $this->articleOrderDetailId;
    }

    /**
     * @param int $deliveryInterval
     *
     * @return Order
     */
    public function setDeliveryInterval($deliveryInterval)
    {
        $this->deliveryInterval = $deliveryInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryInterval()
    {
        return $this->deliveryInterval;
    }

    /**
     * @param string $deliveryIntervalUnit
     *
     * @throws \InvalidArgumentException
     *
     * @return Order
     */
    public function setDeliveryIntervalUnit($deliveryIntervalUnit = null)
    {
        if ($deliveryIntervalUnit !== null && !in_array($deliveryIntervalUnit, $this->validUnits, true)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is no valid duration unit. Valid units are: %s.',
                $deliveryIntervalUnit,
                implode(', ', $this->validUnits)
            ));
        }

        $this->deliveryIntervalUnit = $deliveryIntervalUnit;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryIntervalUnit()
    {
        return $this->deliveryIntervalUnit;
    }

    /**
     * @param int $discountOrderDetailId
     *
     * @return Order
     */
    public function setDiscountOrderDetailId($discountOrderDetailId)
    {
        $this->discountOrderDetailId = $discountOrderDetailId;

        return $this;
    }

    /**
     * @return int
     */
    public function getDiscountOrderDetailId()
    {
        return $this->discountOrderDetailId;
    }

    /**
     * @param int $lastOrderId
     *
     * @return Order
     */
    public function setLastOrderId($lastOrderId)
    {
        $this->lastOrderId = $lastOrderId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLastOrderId()
    {
        return $this->lastOrderId;
    }

    /**
     * @param int $duration
     *
     * @return Order
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $durationUnit
     *
     * @throws \InvalidArgumentException
     *
     * @return Order
     */
    public function setDurationUnit($durationUnit = null)
    {
        if ($durationUnit !== null && !in_array($durationUnit, $this->validUnits, true)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is no valid duration unit. Valid units are: %s.',
                $durationUnit,
                implode(', ', $this->validUnits)
            ));
        }

        $this->durationUnit = $durationUnit;

        return $this;
    }

    /**
     * @return string
     */
    public function getDurationUnit()
    {
        return $this->durationUnit;
    }

    /**
     * @param int $orderId
     *
     * @return Order
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set created
     *
     * @param \DateTime|string $created
     *
     * @return Order
     */
    public function setCreated($created = 'now')
    {
        if (!$created instanceof \DateTime) {
            $created = new \DateTime($created);
        }

        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set dueDate
     *
     * @param \DateTime|string $dueDate
     *
     * @return Order
     */
    public function setDueDate($dueDate = 'now')
    {
        if (!$dueDate instanceof \DateTime) {
            $dueDate = new \DateTime($dueDate);
        }

        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set recentRun
     *
     * @param \DateTime|string $recentRun
     *
     * @return Order
     */
    public function setRecentRun($recentRun = 'now')
    {
        if (!$recentRun instanceof \DateTime) {
            $recentRun = new \DateTime($recentRun);
        }

        $this->recentRun = $recentRun;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRecentRun()
    {
        return $this->recentRun;
    }

    /**
     * Set lastRun
     *
     * @param \DateTime|string $lastRun
     *
     * @return Order
     */
    public function setLastRun($lastRun = 'now')
    {
        if (!$lastRun instanceof \DateTime) {
            $lastRun = new \DateTime($lastRun);
        }

        $this->lastRun = $lastRun;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastRun()
    {
        return $this->lastRun;
    }

    /**
     * @return int
     */
    public function getDelivered()
    {
        return $this->delivered;
    }

    /**
     * @param $delivered
     */
    public function setDelivered($delivered)
    {
        $this->delivered = $delivered;
    }

    /**
     * @return \DateTime
     */
    public function getDateNow()
    {
        if ($this->dateNow === null) {
            $this->dateNow = new \DateTime('now');
        }

        return clone $this->dateNow;
    }

    /**
     * @param \DateTime $dateNow
     *
     * @return Order
     */
    public function setDateNow(\DateTime $dateNow)
    {
        $this->dateNow = $dateNow;

        return $this;
    }

    /**
     * @param \DateTime|null $date
     *
     * @return bool
     */
    public function isDue(\DateTime $date = null)
    {
        if ($date === null) {
            $date = $this->getDateNow();
            $date->setTime(0, 0);
        }

        return $this->getDueDate() <= $date;
    }

    /**
     * @param \DateTime|null $date
     *
     * @return bool
     */
    public function isExpired(\DateTime $date = null)
    {
        if ($this->getEndlessSubscription() && $this->getLastRun() === null) {
            return false;
        }

        if ($date === null) {
            $date = $this->getLastRun();
            $date->setTime(0, 0);
        }

        return $this->getDueDate() > $date;
    }

    /**
     * @param int $lastOrderId
     *
     * @return Order
     */
    public function run($lastOrderId)
    {
        $this->setLastOrderId($lastOrderId);

        $this->setRecentRun($this->getDateNow());
        $this->incrementDueDate();

        if ($this->getDelivered() !== null) {
            $this->setDelivered($this->getDelivered() + 1);
        }

        return $this;
    }

    /**
     * Increments given $startDate $duration-times the given $durationUnit.
     * Return new \DateTime instance.
     *
     * @param \DateTime $startDate
     * @param int       $duration
     * @param string    $durationUnit
     *
     * @return \DateTime
     */
    public function incrementDate(\DateTime $startDate, $duration, $durationUnit)
    {
        $duration = (int) $duration;

        if ($durationUnit === 'weeks') {
            $endDate = clone $startDate;
            $endDate->add(new \DateInterval('P' . $duration . 'W'));

            return $endDate;
        }

        $createdDate = $this->getCreated();
        $endDate = clone $createdDate;
        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($createdDate, $interval, $startDate);
        $monthsDiff = 0;
        foreach ($period as $date) {
            ++$monthsDiff;
        }
        $duration += $monthsDiff;
        $endDate->add(new \DateInterval('P' . $duration . 'M'));
        $createdDay = $createdDate->format('j');
        $endDay = $endDate->format('j');
        if ($createdDay !== $endDay) {
            $endDate->modify('last day of last month');
        }

        return $endDate;
    }

    /**
     * @return bool
     */
    public function getEndlessSubscription()
    {
        return $this->endlessSubscription;
    }

    /**
     * @param bool $endlessSubscription
     */
    public function setEndlessSubscription($endlessSubscription)
    {
        $this->endlessSubscription = $endlessSubscription;
    }

    /**
     * @return string
     */
    public function getPeriodOfNoticeInterval()
    {
        return $this->periodOfNoticeInterval;
    }

    /**
     * @param string $periodOfNoticeInterval
     */
    public function setPeriodOfNoticeInterval($periodOfNoticeInterval)
    {
        $this->periodOfNoticeInterval = $periodOfNoticeInterval;
    }

    /**
     * @return int
     */
    public function getPeriodOfNoticeUnit()
    {
        return $this->periodOfNoticeUnit;
    }

    /**
     * @param int $periodOfNoticeUnit
     */
    public function setPeriodOfNoticeUnit($periodOfNoticeUnit)
    {
        $this->periodOfNoticeUnit = $periodOfNoticeUnit;
    }

    /**
     * @throws \LogicException
     *
     * @return Order
     */
    public function calculateLastRunDate()
    {
        $duration = $this->getDuration();
        $durationUnit = $this->getDurationUnit();

        $createdDate = $this->getCreated();
        if (!($createdDate instanceof \DateTime)) {
            throw new \LogicException('Could not compute date of last run. Created date is missing.');
        }

        $lastRunDate = $this->incrementDate($createdDate, $duration, $durationUnit);

        $this->setLastRun($lastRunDate);

        return $this;
    }

    /**
     * Increments dueDate by $this->interval in unit $this->deliveryIntervalUnit
     *
     * @return Order
     */
    public function incrementDueDate()
    {
        $interval = $this->getDeliveryInterval();
        $unit = $this->getDeliveryIntervalUnit();
        $dueDate = $this->getDueDate();

        if (!($dueDate instanceof \DateTime)) {
            $dueDate = $this->getCreated();
        }

        $dueDate = $this->incrementDate($dueDate, $interval, $unit);

        $this->setDueDate($dueDate);

        return $this;
    }

    /**
     * @return array
     */
    public function getValidUnits()
    {
        return $this->validUnits;
    }

    /**
     * @param array $validUnits
     */
    public function setValidUnits($validUnits)
    {
        $this->validUnits = $validUnits;
    }

    /**
     * @return \Shopware\Models\Order\Detail
     */
    public function getDiscountOrderDetail()
    {
        return $this->discountOrderDetail;
    }

    /**
     * @param \Shopware\Models\Order\Detail $discountOrderDetail
     */
    public function setDiscountOrderDetail($discountOrderDetail)
    {
        $this->discountOrderDetail = $discountOrderDetail;
    }

    /**
     * @return \Shopware\Models\Order\Order
     */
    public function getLastOrder()
    {
        return $this->lastOrder;
    }

    /**
     * @param \Shopware\Models\Order\Order $lastOrder
     */
    public function setLastOrder($lastOrder)
    {
        $this->lastOrder = $lastOrder;
    }

    /**
     * @return bool
     */
    public function getDirectTermination()
    {
        return $this->directTermination;
    }

    /**
     * @param bool $directTermination
     */
    public function setDirectTermination($directTermination)
    {
        $this->directTermination = $directTermination;
    }

    /**
     * @return \DateTime
     */
    public function getTerminationDate()
    {
        return $this->terminationDate;
    }

    /**
     * @param \DateTime $terminationDate
     */
    public function setTerminationDate($terminationDate = null)
    {
        $this->terminationDate = $terminationDate;
    }

    /**
     * @return int|null
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param int|null $paymentId
     */
    public function setPaymentId($paymentId = null)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @return int|null
     */
    public function getBillingAddressId()
    {
        return $this->billingAddressId;
    }

    /**
     * @param int|null $billingAddressId
     */
    public function setBillingAddressId($billingAddressId = null)
    {
        $this->billingAddressId = $billingAddressId;
    }

    /**
     * @return int|null
     */
    public function getShippingAddressId()
    {
        return $this->shippingAddressId;
    }

    /**
     * @param int|null $shippingAddressId
     */
    public function setShippingAddressId($shippingAddressId = null)
    {
        $this->shippingAddressId = $shippingAddressId;
    }

    /**
     * Return an  array representation.
     * Use the $dateFormat to automatically convert the date object to a string with the corresponding format.
     *
     * @param string|null $dateFormat
     *
     * @return array
     */
    public function toArray($dateFormat = null)
    {
        return [
            'id' => $this->getId(),
            'customerId' => $this->getCustomerId(),
            'orderId' => $this->getOrderId(),
            'lastOrderId' => $this->getLastOrderId(),
            'endlessSubscription' => $this->getEndlessSubscription(),
            'articleOrderDetailId' => $this->getArticleOrderDetailId(),
            'discountOrderDetailId' => $this->getDiscountOrderDetailId(),
            'duration' => $this->getDuration(),
            'durationUnit' => $this->getDurationUnit(),
            'deliveryInterval' => $this->getDeliveryInterval(),
            'deliveryIntervalUnit' => $this->getDeliveryIntervalUnit(),
            'periodOfNoticeInterval' => $this->getPeriodOfNoticeInterval(),
            'periodOfNoticeUnit' => $this->getPeriodOfNoticeUnit(),
            'directTermination' => $this->getDirectTermination(),
            'terminationDate' => $this->getTerminationDate(),
            'dueDate' => $this->getDueDate(),
            'lastRun' => $dateFormat ? date_format($this->getLastRun(), $dateFormat) : $this->getLastRun(),
            'recentRun' => $this->getRecentRun(),
            'created' => $dateFormat ? date_format($this->getCreated(), $dateFormat) : $this->getCreated(),
            'isDue' => $this->isDue(),
            'isExpired' => $this->isExpired(),
            'delivered' => $this->getDelivered(),
            'paymentId' => $this->getPaymentId(),
            'billingAddressId' => $this->getBillingAddressId(),
            'shippingAddressId' => $this->getShippingAddressId(),
        ];
    }
}
