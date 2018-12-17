<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

interface CartAccessStrategyInterface
{
    /**
     * @param CartAccessContext $context
     * @param CartAccessResult $cartAccessResult
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult);

    /**
     * @param CartAccessResult $cartAccessResult
     */
    public function addInformation(CartAccessResult $cartAccessResult);
}
