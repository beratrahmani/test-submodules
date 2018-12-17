<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Controller;

use Shopware\B2B\Common\Filter\EqualsFilter;
use Shopware\B2B\Common\Filter\Filter;
use Shopware\B2B\Common\Filter\FilterSubQueryWithLike;
use Shopware\B2B\Common\Filter\LikeFilter;
use Shopware\B2B\Common\Filter\OrFilter;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Repository\SearchStruct;
use Shopware\B2B\Common\Validator\ValidationException;

class GridHelper
{
    const PER_PAGE = 10;

    const ALL_FIELD_FILTER = '_all_';

    /**
     * @var GridRepository
     */
    private $gridRepository;

    /**
     * @internal
     * @var array
     */
    protected $filterTypes = [
        'eq' => EqualsFilter::class,
        'like' => LikeFilter::class,
    ];

    /**
     * @var ValidationException
     */
    private $validationException;

    /**
     * @param GridRepository $gridRepository
     */
    public function __construct(GridRepository $gridRepository)
    {
        $this->gridRepository = $gridRepository;
    }

    /**
     * @param Request $request
     * @param SearchStruct $struct
     * @param array $data
     * @param int $maxPage
     * @param int $currentPage
     * @return array
     */
    public function getGridState(Request $request, SearchStruct $struct, array $data, int $maxPage, int $currentPage): array
    {
        $gridState = [
            'maxPage' => $maxPage,
            'currentPage' => $currentPage,
            'data' => $data,
        ];
        $gridState['sortBy'] = $request->getParam('sort-by');
        $gridState['filters'] = $request->getParam('filters');
        $gridState['uriParams'] = $this->getGridValues($request);

        if ($struct->searchTerm) {
            $gridState['searchTerm'] = $struct->searchTerm;
        }

        return $gridState;
    }

    /**
     * @param Request $request
     * @param $struct
     */
    public function extractSearchDataInStoreFront(Request $request, SearchStruct $struct)
    {
        $this->extractPage($request, $struct);
        $this->extractFilters($request, $struct);
        $this->setOrderBy($request, $struct);
    }

    /**
     * @param Request $request
     * @param SearchStruct $struct
     */
    public function extractSearchDataInBackend(Request $request, SearchStruct $struct)
    {
        $this->extractLimitAndOffsetInBackend($request, $struct);
        $this->extractFiltersInBackend($request, $struct);
        $this->setOrderByInBackend($request, $struct);
    }

    /**
     * @param Request $request
     * @param SearchStruct $struct
     */
    public function extractSearchDataInRestApi(Request $request, SearchStruct $struct)
    {
        $this->extractLimitAndOffset($request, $struct);
        $this->extractFilters($request, $struct);
    }

    /**
     * @param int $totalCount
     * @return int
     */
    public function getMaxPage(int $totalCount): int
    {
        return (int) ceil($totalCount / self::PER_PAGE);
    }

    /**
     * @param ValidationException $violations
     */
    public function pushValidationException(ValidationException $violations)
    {
        $this->validationException = $violations;
    }

    /**
     * @return ValidationException
     */
    public function popValidationException()
    {
        $violations = $this->validationException;
        $this->validationException = null;

        return $violations;
    }

    /**
     * @return bool
     */
    public function hasValidationException(): bool
    {
        return (bool) $this->validationException;
    }

    /**
     * @internal
     * @param Request $request
     * @param SearchStruct $struct
     * @throws \InvalidArgumentException
     */
    protected function setOrderBy(Request $request, SearchStruct $struct)
    {
        $orderBy = $request->getParam('sort-by', '');

        if (!$orderBy) {
            return;
        }

        $orderByExpression = explode('::', $orderBy);

        if (count($orderByExpression) !== 2) {
            throw new \InvalidArgumentException('Incorrect order by argument');
        }

        $struct->orderBy = $orderByExpression[0];
        $struct->orderDirection = $orderByExpression[1];
    }

    /**
     * @internal
     * @param Request $request
     * @param SearchStruct $struct
     * @throws \InvalidArgumentException
     */
    protected function setOrderByInBackend(Request $request, SearchStruct $struct)
    {
        $orderBy = $request->getParam('sort', []);

        if (!$orderBy) {
            return;
        }

        $struct->orderBy = $orderBy[0]['property'];
        $struct->orderDirection = $orderBy[0]['direction'];
    }

    /**
     * @internal
     * @param Request $request
     * @param SearchStruct $struct
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    protected function extractFilters(Request $request, SearchStruct $struct)
    {
        foreach ($request->getParam('filters', []) as $filterArray) {
            $fieldName = $this->extractFilterFieldName($filterArray);

            $type = $this->extractFilterType($filterArray);

            if ($this->hasFilterValue($filterArray)) {
                continue;
            }
            $value = $filterArray['value'];

            if ($fieldName === self::ALL_FIELD_FILTER) {
                $struct->searchTerm = $value;
                $struct->filters[] = $this->createAllFieldFilter($value);

                return;
            }

            $struct->filters[] = $this->createFilterClass($type, $fieldName, $value);
        }
    }

    /**
     * @internal
     * @param Request $request
     * @param SearchStruct $struct
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    protected function extractFiltersInBackend(Request $request, SearchStruct $struct)
    {
        $filter = $request->getParam('filter', []);

        if (!$filter) {
            return;
        }

        if ($filter[0]['property'] !== 'search') {
            return;
        }

        $value = $filter[0]['value'];

        $struct->searchTerm = $value;
        $struct->filters[] = $this->createAllFieldFilter($value);
    }

    /**
     * @internal
     * @param Request $request
     * @param SearchStruct $struct
     */
    protected function extractPage(Request $request, SearchStruct $struct)
    {
        $currentPage = ((int) $request->getParam('page', 1)) - 1;

        $struct->offset = $currentPage * self::PER_PAGE;
        $struct->limit = self::PER_PAGE;
    }

    /**
     * @internal
     * @param Request $request
     * @param SearchStruct $struct
     */
    protected function extractLimitAndOffset(Request $request, SearchStruct $struct)
    {
        $struct->offset = $request->getParam('offset', null);
        $struct->limit = $request->getParam('limit', null);
    }

    /**
     * @internal
     * @param Request $request
     * @param SearchStruct $struct
     */
    protected function extractLimitAndOffsetInBackend(Request $request, SearchStruct $struct)
    {
        $struct->offset = (int) $request->getParam('start', null);
        $struct->limit = (int) $request->getParam('limit', null);
    }

    /**
     * @internal
     * @param Request $request
     * @return array
     */
    protected function getGridValues(Request $request): array
    {
        $possibleKeys = [
            'sort-by',
            'filters',
            'page',
        ];

        $data = [];
        foreach ($possibleKeys as $key) {
            $value = $request->getParam($key);

            if (!$value) {
                continue;
            }

            $data[$key] = $value;
        }

        return $this->explodePaths($data);
    }

    /**
     * @param array $multiDimensionalArray
     * @param array $pathParts
     * @return array
     */
    public function explodePaths(array $multiDimensionalArray, array $pathParts = []): array
    {
        $result = [];

        foreach ($multiDimensionalArray as $key => $value) {
            $currentPathParts = array_merge($pathParts, [$key]);

            if (is_array($value)) {
                $explodedPaths = $this->explodePaths($value, $currentPathParts);

                foreach ($explodedPaths as $explodedKey => $explodedValue) {
                    $result[$explodedKey] = $explodedValue;
                }
                continue;
            }

            $pathName = $currentPathParts[0];
            $countCurrentPathParts = count($currentPathParts);
            for ($i = 1; $i < $countCurrentPathParts; $i++) {
                $pathName .= '[' . $currentPathParts[$i] . ']';
            }

            $result[$pathName] = $value;
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function getCurrentPage(Request $request): int
    {
        return (int) $request->getParam('page', 1);
    }

    /**
     * @internal
     * @param array $filterArray
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function extractFilterFieldName(array $filterArray): string
    {
        if (!array_key_exists('field-name', $filterArray)) {
            throw new \InvalidArgumentException('Missing required filter parameter "field-name"');
        }

        return $filterArray['field-name'];
    }

    /**
     * @internal
     * @param array $filterArray
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function extractFilterType(array $filterArray): string
    {
        if (!array_key_exists('type', $filterArray)) {
            throw new \InvalidArgumentException('Missing required filter parameter "type"');
        }
        $type = $filterArray['type'];

        if (!array_key_exists($type, $this->filterTypes)) {
            throw new \DomainException(sprintf('Invalid required filter type "%s"', $type));
        }

        return $type;
    }

    /**
     * @internal
     * @param array $filterArray
     * @return bool
     */
    protected function hasFilterValue(array $filterArray): bool
    {
        return !array_key_exists('value', $filterArray)
            || null === $filterArray['value']
            || '' === $filterArray['value'];
    }

    /**
     * @internal
     * @param string $type
     * @param string $fieldName
     * @param mixed $value
     * @return Filter
     */
    protected function createFilterClass(string $type, string $fieldName, $value): Filter
    {
        $filterClass = $this->filterTypes[$type];

        return new $filterClass($this->gridRepository->getMainTableAlias(), $fieldName, $value);
    }

    /**
     * @internal
     * @param string $value
     * @return Filter
     */
    protected function createAllFieldFilter(string $value): Filter
    {
        $likeFilters = array_merge(
            $this->getFullTextSearchFiledLikeFilters($value),
            $this->getFullTextSearchAdditionalResourcesLikeFilters($value)
        );

        return new OrFilter($likeFilters);
    }

    /**
     * @internal
     * @param string $value
     * @return array
     */
    protected function getFullTextSearchFiledLikeFilters(string $value): array
    {
        $likeFilters = [];
        foreach ($this->gridRepository->getFullTextSearchFields() as $searchField) {
            $likeFilters[] = new LikeFilter($this->gridRepository->getMainTableAlias(), $searchField, $value);
        }

        return $likeFilters;
    }

    /**
     * @internal
     * @param string $value
     * @return array
     */
    protected function getFullTextSearchAdditionalResourcesLikeFilters(string $value): array
    {
        $likeFilters = [];

        foreach ($this->gridRepository->getAdditionalSearchResourceAndFields() as $alias => $searchFields) {
            foreach ($searchFields as $tableAlias => $searchField) {
                if (!is_array($searchField)) {
                    $likeFilters[] = new FilterSubQueryWithLike($tableAlias, $alias, $searchField, $value);
                    continue;
                }

                foreach ($searchField as $field) {
                    $likeFilters[] = new FilterSubQueryWithLike($tableAlias, $alias, $field, $value);
                }
            }
        }

        return $likeFilters;
    }

    /**
     * @param string $propertyName
     * @return array view response
     */
    public function getValidationResponse(string $propertyName): array
    {
        if (!$this->hasValidationException()) {
            return [];
        }

        $validationException = $this->popValidationException();

        $errors = [];

        foreach ($validationException->getViolations() as $violation) {
            $errors[] = [
                'property' => ucfirst($violation->getPropertyPath()),
                'snippetKey' => str_replace(['}', '{', '%', '.', ' '], '', $violation->getMessageTemplate()),
                'messageTemplate' => $violation->getMessageTemplate(),
                'parameters' => $violation->getParameters(),
                'cause' => $violation->getCause(),
            ];
        };

        return [
            $propertyName => $validationException->getEntity(),
            'errors' => $errors,
        ];
    }
}
