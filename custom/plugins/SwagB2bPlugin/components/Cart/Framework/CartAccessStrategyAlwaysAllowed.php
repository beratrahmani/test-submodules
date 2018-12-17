<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework;

/**
 * The null object pattern @see https://en.wikipedia.org/wiki/Null_Object_pattern
 */
class CartAccessStrategyAlwaysAllowed implements CartAccessStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function checkAccess(CartAccessContext $context, CartAccessResult $cartAccessResult)
    {
        //nth
    }

    /**
     * {@inheritdoc}
     */
    public function addInformation(CartAccessResult $cartAccessResult)
    {
        //nth
    }
}
