<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Filter;

class OrFilter implements Filter
{
    /**
     * @var Filter[]
     */
    private $filters;

    /**
     * @param Filter[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterResponse(string $paramPrefix): FilterResponse
    {
        $queryParts = [];
        $params = [];

        foreach ($this->filters as $index => $filter) {
            $response = $filter->getFilterResponse($paramPrefix . '_' . $index);
            $queryParts[] = '(' . $response->queryPart . ')';
            $params = array_merge($params, $response->params);
        }

        return new FilterResponse(
            '(' . implode(' OR ', $queryParts) . ')',
            $params
        );
    }
}
