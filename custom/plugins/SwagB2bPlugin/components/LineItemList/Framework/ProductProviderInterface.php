<?php declare(strict_types=1);

namespace Shopware\B2B\LineItemList\Framework;

use InvalidArgumentException;

interface ProductProviderInterface
{
    /**
     * @param LineItemList $list
     */
    public function updateList(LineItemList $list);

    /**
     * @param string $price
     * @param string $locale
     * @throws InvalidArgumentException
     * @return float
     */
    public static function convertPriceToLocale(string $price, string $locale = 'en_EN'): float;

    /**
     * @param LineItemReference $lineItemReference
     */
    public function updateReference(LineItemReference $lineItemReference);

    /**
     * @param string $productNumber
     * @return bool
     */
    public function isProduct(string $productNumber): bool;

    /**
     * @param LineItemReference $lineItemReference
     */
    public function setMaxMinAndSteps(LineItemReference $lineItemReference);
}
