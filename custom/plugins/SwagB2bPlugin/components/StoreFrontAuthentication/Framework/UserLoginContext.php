<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

class UserLoginContext
{
    /**
     * @var int
     */
    public $subShopId;

    /**
     * @var string
     */
    public $customerGroupName;

    /**
     * @var int
     */
    public $paymentId;

    /**
     * @var int
     */
    public $paymentPreset;

    /**
     * @return string
     */
    public $avatar;

    /**
     * @param int $subShopId
     * @param string $customerGroupName
     * @param int $paymentId
     * @param string $avatar
     * @param int $paymentPreset
     */
    public function __construct(int $subShopId, string $customerGroupName, int $paymentId, string $avatar, int $paymentPreset = 0)
    {
        $this->subShopId = $subShopId;
        $this->customerGroupName = $customerGroupName;
        $this->paymentId = $paymentId;
        $this->avatar = $avatar;
        $this->paymentPreset = $paymentPreset;
    }
}
