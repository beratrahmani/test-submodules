<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Controller;

interface GridRepository
{
    /**
     * @return string query alias for filter construction
     */
    public function getMainTableAlias(): string;

    /**
     * @return string[]
     */
    public function getFullTextSearchFields(): array;

    /**
     * @return array
     */
    public function getAdditionalSearchResourceAndFields(): array;
}
