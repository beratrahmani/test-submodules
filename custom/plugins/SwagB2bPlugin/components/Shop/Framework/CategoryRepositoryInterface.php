<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Framework;

interface CategoryRepositoryInterface
{
    /**
     * @param int $parentId
     * @return CategoryNode[]
     */
    public function fetchChildren(int $parentId): array;

    /**
     * @param int $categoryId
     * @param string $productNumber
     * @return bool
     */
    public function hasProduct(int $categoryId, string $productNumber): bool;

    /**
     * @param int $categoryId
     * @return CategoryNode
     */
    public function fetchNodeById(int $categoryId): CategoryNode;
}
