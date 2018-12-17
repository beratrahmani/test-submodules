<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Currency\Framework\CurrencyContext;

use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

interface CreateOfferThroughCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function createOffer(Identity $identity, CurrencyContext $currencyContext): OfferEntity;
}
