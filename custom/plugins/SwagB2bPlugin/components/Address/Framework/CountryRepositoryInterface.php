<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

interface CountryRepositoryInterface
{
    /**
     * @return array
     */
    public function getCountryList(): array;
}
