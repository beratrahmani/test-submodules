<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Filter;

use Doctrine\DBAL\Query\QueryBuilder;

class QueryExtender
{
    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @param Filter[] $filters
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function extendQueryBuilder(array $filters, QueryBuilder $queryBuilder): QueryBuilder
    {
        foreach ($filters as $filter) {
            $filterResponse = $filter->getFilterResponse($this->getNextPrefix());

            $queryBuilder->andWhere($filterResponse->queryPart);

            foreach ($filterResponse->params as $alias => $value) {
                $queryBuilder->setParameter($alias, $value);
            }
        }

        return $queryBuilder;
    }

    /**
     * @internal
     * @return string
     */
    protected function getNextPrefix(): string
    {
        return 'p_' . $this->counter++;
    }
}
