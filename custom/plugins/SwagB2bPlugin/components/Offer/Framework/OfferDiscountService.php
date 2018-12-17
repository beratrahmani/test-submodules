<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;

class OfferDiscountService
{
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferCrudService
     */
    private $offerCrudService;

    /**
     * @param OfferRepository $offerRepository
     * @param OfferCrudService $offerCrudService
     */
    public function __construct(
        OfferRepository $offerRepository,
        OfferCrudService $offerCrudService
    ) {
        $this->offerRepository = $offerRepository;
        $this->offerCrudService = $offerCrudService;
    }

    /**
     * @param int $offerId
     * @param CurrencyContext $currencyContext
     * @param float $discount
     * @param Identity $identity
     * @param bool $isBackend
     * @return OfferEntity
     */
    public function updateDiscount(
        int $offerId,
        CurrencyContext $currencyContext,
        float $discount,
        Identity $identity,
        bool $isBackend
    ): OfferEntity {
        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        $offer->discountValueNet = $discount;

        $serviceRequest = $this->offerCrudService
            ->createExistingRecordRequest($offer->toArray());

        return $this->offerCrudService->update($serviceRequest, $currencyContext, $identity, $isBackend);
    }

    /**
     * @param int $offerId
     * @param CurrencyContext $currencyContext
     * @param Identity $identity
     * @param bool $isBackend
     * @throws DiscountGreaterThanAmountException
     */
    public function checkOfferDiscountGreaterThanAmount(
        int $offerId,
        CurrencyContext $currencyContext,
        Identity $identity,
        bool $isBackend = false
    ) {
        $offer = $this->offerRepository
            ->fetchOfferById($offerId, $currencyContext, $identity->getOwnershipContext());

        if ($offer->discountAmountNet >= 0) {
            return;
        }

        $this->updateDiscount($offerId, $currencyContext, (float) 0, $identity, $isBackend);

        throw new DiscountGreaterThanAmountException();
    }
}
