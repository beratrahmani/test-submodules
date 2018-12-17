<?php declare(strict_types = 1);

namespace Shopware\B2B\Common\Filter;

class FilterSubQueryWithLike implements Filter
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var float|int|string
     */
    private $value;

    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $tableAlias;

    /**
     * @param $tableAlias
     * @param string $query
     * @param string $fieldName
     * @param string|int|float $value
     */
    public function __construct($tableAlias, string $query, string $fieldName, $value)
    {
        $this->query = $query;
        $this->fieldName = $fieldName;
        $this->value = $value;
        $this->tableAlias = $tableAlias;
    }

    /**
     * @param string $paramPrefix
     * @return FilterResponse
     */
    public function getFilterResponse(string $paramPrefix): FilterResponse
    {
        if (!is_string($this->tableAlias)) {
            $this->tableAlias = $paramPrefix;
        }

        $innerLikeFilterResponse = (new LikeFilter($this->tableAlias, $this->fieldName, $this->value))
            ->getFilterResponse($this->tableAlias);

        return new FilterResponse(
            sprintf($this->query, $this->tableAlias, $innerLikeFilterResponse->queryPart),
            $innerLikeFilterResponse->params
        );
    }
}
