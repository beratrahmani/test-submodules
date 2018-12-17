<?php declare(strict_types = 1);

namespace Shopware\B2B\Common\Filter;

class FilterSubQueryWithEquals implements Filter
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
     * @param string $query
     * @param string $fieldName
     * @param string|int|float $value
     */
    public function __construct(string $query, string $fieldName, $value)
    {
        $this->query = $query;
        $this->fieldName = $fieldName;
        $this->value = $value;
    }

    /**
     * @param string $paramPrefix
     * @return FilterResponse
     */
    public function getFilterResponse(string $paramPrefix): FilterResponse
    {
        $innerLikeFilterResponse = (new EqualsFilter($paramPrefix, $this->fieldName, $this->value))
            ->getFilterResponse($paramPrefix);

        return new FilterResponse(
            sprintf($this->query, $paramPrefix, $innerLikeFilterResponse->queryPart),
            $innerLikeFilterResponse->params
        );
    }
}
