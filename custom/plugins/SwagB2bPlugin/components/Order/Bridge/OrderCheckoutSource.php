<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Bridge;

use Shopware\B2B\Order\Framework\OrderSource;

class OrderCheckoutSource implements OrderSource
{
    /**
     * @var array
     */
    public $basketData;

    /**
     * @var int
     */
    public $billingAddressId;

    /**
     * @var int
     */
    public $shippingAddressId;

    /**
     * @var int
     */
    public $dispatchId;

    /**
     * @var string
     */
    public $sComment;

    /**
     * @var string
     */
    public $deviceType;

    /**
     * @var float
     */
    public $shippingAmount = 0.0;

    /**
     * @var float
     */
    public $shippingAmountNet = 0.0;

    /**
     * @param array $basketData
     * @param int $billingAddressId
     * @param int $shippingAddressId
     * @param int $dispatchId
     * @param string $sComment
     * @param string $deviceType
     */
    public function __construct(
        array $basketData,
        int $billingAddressId,
        int $shippingAddressId,
        int $dispatchId,
        string $sComment,
        string $deviceType
    ) {
        $this->basketData = $basketData;
        $this->billingAddressId = $billingAddressId;
        $this->shippingAddressId = $shippingAddressId;
        $this->dispatchId = $dispatchId;
        $this->sComment = $sComment;
        $this->deviceType = $deviceType;

        if (isset($basketData['sShippingcostsWithTax']) && isset($basketData['sShippingcostsNet'])) {
            $this->shippingAmount = (float) $basketData['sShippingcostsWithTax'];
            $this->shippingAmountNet = (float) $basketData['sShippingcostsNet'];
        }
    }
}
