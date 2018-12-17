<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Filter;

class FilterResponse
{
    /**
     * @var string
     */
    public $queryPart;

    /**
     * @var array
     */
    public $params;

    /**
     * @param string $queryPart
     * @param array $params
     */
    public function __construct(string $queryPart = '', array $params = [])
    {
        $this->queryPart = $queryPart;
        $this->params = $params;
    }

    /**
     * @param string $queryPart
     * @return FilterResponse
     */
    public function addQueryPart(string $queryPart): self
    {
        $this->queryPart .= ' ' . $queryPart;

        return $this;
    }

    /**
     * @param array $params
     * @return FilterResponse
     */
    public function addParams(array $params): self
    {
        $this->params = array_merge(
            $this->params,
            $params
        );

        return $this;
    }
}
