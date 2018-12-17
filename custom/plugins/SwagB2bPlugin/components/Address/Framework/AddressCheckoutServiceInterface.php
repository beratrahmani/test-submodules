<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

interface AddressCheckoutServiceInterface
{
    /**
     * Set the Address to checkout against
     *
     * @param string $type
     * @param AddressEntity $address
     */
    public function updateCheckoutAddress(string $type, AddressEntity $address);
}
