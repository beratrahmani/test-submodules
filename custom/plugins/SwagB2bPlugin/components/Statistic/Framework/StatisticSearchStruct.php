<?php declare(strict_types=1);

namespace Shopware\B2B\Statistic\Framework;

use Shopware\B2B\Common\Repository\SearchStruct;

class StatisticSearchStruct extends SearchStruct
{
    /**
     * @var string
     */
    public $groupBy;

    /**
     * @var \DateTime
     */
    public $from;

    /**
     * @var \DateTime
     */
    public $to;
}
