<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

class ContingentRuleOrder
{
    /**
     * @var int
     */
    public $orderAmount;

    /**
     * @var int
     */
    public $orderQuantity;

    /**
     * @var int
     */
    public $orderItemQuantity;

    /**
     * @param array $data
     */
    public function fromDatabaseArray(array $data)
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }
}
