<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Filter;

interface Filter
{
    /**
     * @param string $paramPrefix
     * @return FilterResponse
     */
    public function getFilterResponse(string $paramPrefix): FilterResponse;
}
