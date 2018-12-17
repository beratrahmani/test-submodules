<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

class ContingentRuleService
{
    /**
     * @return array
     */
    public function getTimeRestrictions(): array
    {
        return [
            'DAYOFYEAR',
            'YEARWEEK',
            'MONTH',
            'QUARTER',
            'YEAR',
        ];
    }
}
