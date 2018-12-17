<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Filter;

class NotEqualsFilter implements Filter
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
     * @var string|int|float
     */
    private $value;

    /**
     * @param string $tableAlias
     * @param string $fieldName
     * @param string|int|float $value
     */
    public function __construct(string $tableAlias, string $fieldName, $value)
    {
        $this->tableAlias = $tableAlias;
        $this->fieldName = $fieldName;
        $this->value = $value;
    }

    /**
     * @param string $paramPrefix
     * @return FilterResponse
     */
    public function getFilterResponse(string $paramPrefix): FilterResponse
    {
        return new FilterResponse(
            $this->tableAlias . '.' . $this->fieldName . ' != :' . $paramPrefix,
            [$paramPrefix => $this->value]
        );
    }
}
