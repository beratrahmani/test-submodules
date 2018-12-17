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

namespace SwagAboCommerce\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use SwagAboCommerce\Models\Order;

class OrderTest extends TestCase
{
    /**
     * @var array
     */
    private $testData = [
        'customerId' => 5,
        'orderId' => 5,
        'articleOrderDetailId' => 5,
        'discountOrderDetailId' => 5,
        'duration' => 1337,
        'durationUnit' => 'weeks',
        'deliveryInterval' => 5,
        'deliveryIntervalUnit' => 'months',
    ];

    public function testGetterAndSetter()
    {
        $order = $this->getOrder();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);
            $getMethod = 'get' . ucfirst($field);

            $order->$setMethod($value);

            $this->assertEquals($order->$getMethod(), $value);
        }
    }

    public function testFromArrayWorks()
    {
        $order = $this->getOrder();
        $order->fromArray($this->testData);

        foreach ($this->testData as $fieldName => $value) {
            $getMethod = 'get' . ucfirst($fieldName);
            $this->assertEquals($order->$getMethod(), $value);
        }
    }

    public function testToArrayWorks()
    {
        $order = $this->getOrder();
        $order->fromArray($this->testData);

        $this->assertGreaterThan(5, $order->toArray());
    }

    /**
     * @dataProvider dueDateByWeeksProvider
     *
     * @param string $date
     * @param int    $interval
     * @param string $expected
     */
    public function testIncrementDueDateByWeeks($date, $interval, $expected)
    {
        $order = $this->getOrder();

        $initialDueDate = new \DateTime($date);
        $intervalUnit = 'weeks';
        $expectedDate = new \DateTime($expected);

        // Set initial values
        $order->setDueDate($initialDueDate);
        $order->setDeliveryInterval($interval);
        $order->setDeliveryIntervalUnit($intervalUnit);

        // increment due date
        $order->incrementDueDate();

        $this->assertEquals($order->getDueDate(), $expectedDate);
    }

    /**
     * @return array
     */
    public function dueDateByWeeksProvider()
    {
        return[
            ['2012-12-07', 3, '2012-12-28'],
            ['2018-01-28', 4, '2018-02-25'],
            ['2018-01-28', 13, '2018-04-29'],
        ];
    }

    /**
     * @dataProvider dueDateByMonthProvider
     *
     * @param string $dueDate
     * @param int    $interval
     * @param string $expected
     * @param string $created
     */
    public function testIncrementDueDateByMonths($dueDate, $interval, $expected, $created)
    {
        $order = $this->getOrder();

        $initialDueDate = new \DateTime($dueDate);
        $intervalUnit = 'months';
        $expectedDate = new \DateTime($expected);

        // Set initial values
        $order->setDueDate($initialDueDate);
        $order->setDeliveryInterval($interval);
        $order->setDeliveryIntervalUnit($intervalUnit);
        $order->setCreated($created);

        // increment due date
        $order->incrementDueDate();

        $this->assertEquals($order->getDueDate(), $expectedDate);
    }

    /**
     * @return array
     */
    public function dueDateByMonthProvider()
    {
        return[
            ['2018-01-28', 1, '2018-02-28', '2018-01-28'],
            ['2018-01-31', 3, '2018-04-30', '2018-01-31'],
            ['2018-01-31', 1, '2018-02-28', '2018-01-31'],
            ['2018-01-15', 1, '2018-02-15', '2018-01-15'],
            ['2018-03-02', 1, '2018-03-31', '2018-01-31'],
            ['2018-01-30', 13, '2019-02-28', '2018-01-30'],
            ['2018-10-31', 1, '2018-11-30', '2018-10-31'],
            ['2018-12-30', 1, '2019-01-30', '2018-12-30'],
            ['2018-03-31', 1, '2018-04-30', '2018-01-31'],
            ['2018-02-28', 1, '2018-03-31', '2018-01-31'],
            ['2018-03-31', 2, '2018-05-31', '2018-01-31'],
            ['2017-12-31', 2, '2018-02-28', '2017-12-31'],
            ['2018-02-28', 2, '2018-04-30', '2017-12-31'],
        ];
    }

    public function testisDue()
    {
        $order = $this->getOrder();

        $initialDueDate = new \DateTime('2012-12-01');
        $dateNow = new \DateTime('2012-12-01 13:33:37');

        $dateEarlier = new \DateTime('2012-11-05 13:33:37');
        $dateLater = new \DateTime('2012-12-05 13:33:37');

        $order->setDueDate($initialDueDate);
        $order->setDateNow($dateNow);

        $this->assertTrue($order->isDue());
        $this->assertTrue($order->isDue($dateLater));
        $this->assertFalse($order->isDue($dateEarlier));
    }

    public function testSetDurationUnitWithInvalidUnitShouldThrowException()
    {
        $order = $this->getOrder();

        $this->expectException(\InvalidArgumentException::class);
        $order->setDurationUnit('foo');
    }

    public function testSetDeliveryIntervalUnitWithInvalidUnitShouldThrowException()
    {
        $order = $this->getOrder();

        $this->expectException(\InvalidArgumentException::class);
        $order->setDeliveryIntervalUnit('foo');
    }

    public function testRun()
    {
        $order = $this->getOrder();

        $initialDueDate = new \DateTime('2012-12-01');
        $order->setDueDate(clone $initialDueDate);

        $dateNow = new \DateTime('2012-12-02 13:33:37');
        $order->setDateNow(clone $dateNow);

        $lastOrderId = 55;
        $order->run($lastOrderId);

        $this->assertEquals($order->getRecentRun(), $dateNow, 'Recent run should be updated to current date');
        $this->assertEquals($order->getLastOrderId(), $lastOrderId, 'last orderId should be updated');

        //After running the initial order (like in this case) the delivered value should be increased from 1 to 2
        $this->assertEquals($order->getDelivered(), 2, 'Delivered should be 2');
        $this->assertGreaterThan($initialDueDate, $order->getDueDate(), 'dueDate should be updated');
    }

    /**
     * @dataProvider lastRunProvider
     *
     * @param string $createDate
     * @param int    $duration
     * @param string $expectedDate
     */
    public function testCalculateLastRunDate($createDate, $duration, $expectedDate)
    {
        $order = $this->getOrder();
        $order->setDurationUnit('months');
        $order->setCreated($createDate);
        $order->setDuration($duration);

        $order->calculateLastRunDate();

        $this->assertEquals($order->getLastRun()->format('Y-m-d'), $expectedDate);
    }

    /**
     * @return array
     */
    public function lastRunProvider()
    {
        return [
            ['2018-01-31', 13, '2019-02-28'],
            ['2018-01-31', 2, '2018-03-31'],
            ['2018-01-31', 6, '2018-07-31'],
            ['2018-01-31', 1, '2018-02-28'],
            ['2018-01-31', 5, '2018-06-30'],
        ];
    }

    /**
     * @return Order
     */
    private function getOrder()
    {
        return new Order(
            5,
            3,
            1,
            2,
            5,
            'weeks',
            8,
            'weeks'
        );
    }
}
