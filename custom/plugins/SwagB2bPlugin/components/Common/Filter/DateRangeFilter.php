<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Filter;

use Shopware\B2B\Common\Repository\MysqlRepository;

class DateRangeFilter implements Filter
{
    /**
     * @var string
     */
    private $tableAlias;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var \DateTime
     */
    private $from;

    /**
     * @var \DateTime
     */
    private $to;

    /**
     * @param string $tableAlias
     * @param string $fieldName
     * @param \DateTime $from
     * @param \DateTime $to
     */
    public function __construct(string $tableAlias, string $fieldName, \DateTime $from, \DateTime $to)
    {
        $this->tableAlias = $tableAlias;
        $this->fieldName = $fieldName;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @param string $paramPrefix
     * @return FilterResponse
     */
    public function getFilterResponse(string $paramPrefix): FilterResponse
    {
        $fromDateFieldAlias = $paramPrefix . '_1';
        $toDateFieldAlias = $paramPrefix . '_2';

        return new FilterResponse(
            $this->tableAlias . '.' . $this->fieldName . ' BETWEEN :' . $fromDateFieldAlias . ' AND :' . $toDateFieldAlias,
            [
                $fromDateFieldAlias => $this->from->format(MysqlRepository::MYSQL_DATETIME_FORMAT),
                $toDateFieldAlias => $this->to->format(MysqlRepository::MYSQL_DATETIME_FORMAT),
            ]
        );
    }
}
