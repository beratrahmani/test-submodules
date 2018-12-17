<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

class UserOrderCredentials
{
    /**
     * @var string
     */
    public $customerNumber;

    /**
     * @var int
     */
    public $orderUserId;

    /**
     * @var string
     */
    public $orderUserReference;

    /**
     * @param string $customerNumber
     * @param int $orderUserId
     * @param string $orderUserReference
     */
    public function __construct(
        string $customerNumber,
        int $orderUserId,
        string $orderUserReference
    ) {
        $this->customerNumber = $customerNumber;
        $this->orderUserId = $orderUserId;
        $this->orderUserReference = $orderUserReference;
    }
}
