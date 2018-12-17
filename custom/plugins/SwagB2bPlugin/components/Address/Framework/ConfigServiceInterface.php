<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

interface ConfigServiceInterface
{
    /**
     * @param AddressEntity $address
     * @return array
     */
    public function getRequiredFields(): array;

    /**
     * @param AddressEntity $address
     * @return array
     */
    public function getRequiredFieldsByAddress(AddressEntity $address): array;
}
