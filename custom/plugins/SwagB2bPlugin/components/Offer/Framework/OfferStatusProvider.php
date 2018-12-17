<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

class OfferStatusProvider
{
    /**
     * @param array $offers
     */
    public function determinateStatusForOffers(array $offers)
    {
        foreach ($offers as $offer) {
            $this->determinateStatusForOffer($offer);
        }
    }

    /**
     * @param OfferEntity $offer
     */
    public function determinateStatusForOffer(OfferEntity $offer)
    {
        switch (true) {
            case isset($offer->convertedAt):
                $offer->status = OfferEntity::STATE_CONVERTED;
                break;

            case (isset($offer->expiredAt) && $offer->expiredAt < new \DateTime()):
                $offer->status = OfferEntity::STATE_EXPIRED;
                break;

            case isset($offer->acceptedByAdminAt, $offer->acceptedByUserAt):
                $offer->status = OfferEntity::STATE_ACCEPTED_OF_BOTH;
                break;

            case $this->isChangeOfUser($offer):
                break;

            case $this->isChangeOfAdmin($offer):
                break;
            default:
                $offer->status = OfferEntity::STATE_OPEN;
        }
    }

    /**
     * @param OfferEntity $offer
     * @return bool
     */
    protected function isChangeOfUser(OfferEntity $offer): bool
    {
        switch (true) {
            case isset($offer->acceptedByUserAt):
                $offer->status = OfferEntity::STATE_ACCEPTED_USER;

                return true;
            case isset($offer->declinedByUserAt):
                $offer->status = OfferEntity::STATE_DECLINED_USER;

                return true;
            default:
                return false;
        }
    }

    /**
     * @param OfferEntity $offer
     * @return bool
     */
    protected function isChangeOfAdmin(OfferEntity $offer): bool
    {
        switch (true) {
            case isset($offer->acceptedByAdminAt):
                $offer->status = OfferEntity::STATE_ACCEPTED_ADMIN;

                return true;
            case isset($offer->declinedByAdminAt):
                $offer->status = OfferEntity::STATE_DECLINED_ADMIN;

                return true;
            default:
                return false;
        }
    }
}
