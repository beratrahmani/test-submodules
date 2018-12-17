<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Repository;

use Shopware\B2B\Common\Filter\Filter;

class SearchStruct
{
    /**
     * @var Filter[]
     */
    public $filters = [];

    /**
     * @var int
     */
    public $limit;

    /**
     * @var int
     */
    public $offset;

    /**
     * @var string
     */
    public $orderBy;

    /**
     * @var string
     */
    public $orderDirection = 'ASC';

    /**
     * @var string
     */
    public $searchTerm;
}
