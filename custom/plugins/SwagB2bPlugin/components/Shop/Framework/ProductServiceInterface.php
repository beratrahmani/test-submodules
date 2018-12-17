<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Framework;

interface ProductServiceInterface
{
    /**
     * @param string $orderNumber
     * @return string
     */
    public function fetchProductNameByOrderNumber(string $orderNumber): string;

    /**
     * @param array $orderNumbers
     * @return array
     */
    public function fetchProductNamesByOrderNumbers(array $orderNumbers): array;

    /**
     * @param string $term
     * @param int $limit
     * @return array|\string[] $productNumber => $productName
     */
    public function searchProductsByNameOrOrderNumber(string $term, int $limit): array;
}
