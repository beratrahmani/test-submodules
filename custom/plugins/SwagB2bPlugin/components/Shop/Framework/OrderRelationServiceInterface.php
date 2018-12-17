<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Framework;

interface OrderRelationServiceInterface
{
    /**
     * @param int $shippingId
     * @return string
     */
    public function getShippingNameForId(int $shippingId): string;

    /**
     * @param int $paymentId
     * @return string
     */
    public function getPaymentNameForId(int $paymentId): string;
}
