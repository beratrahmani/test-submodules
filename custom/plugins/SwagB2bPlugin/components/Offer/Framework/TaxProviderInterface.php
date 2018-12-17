<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface TaxProviderInterface
{
    /**
     * @param int $lineItemListId
     * @param OwnershipContext $ownershipContext
     * @return float
     */
    public function getDiscountTax(int $lineItemListId, OwnershipContext $ownershipContext): float;

    /**
     * @param OfferLineItemReferenceEntity $reference
     * @return float
     */
    public function getProductTax(OfferLineItemReferenceEntity $reference): float;
}
