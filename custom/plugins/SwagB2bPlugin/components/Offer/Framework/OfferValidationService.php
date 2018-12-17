<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OfferValidationService
{
    const DISCOUNT_GREATER_THAN_AMOUNT = 'DiscountGreaterThanAmount';

    /**
     * @var ValidationBuilder
     */
    private $validationBuilder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var OfferLineItemReferenceRepository
     */
    private $referenceRepository;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param OfferLineItemReferenceRepository $referenceRepository
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        OfferLineItemReferenceRepository $referenceRepository
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * @param OfferEntity $offer
     * @param OwnershipContext $ownershipContext
     * @return Validator
     */
    public function createInsertValidation(OfferEntity $offer, OwnershipContext $ownershipContext): Validator
    {
        return $this->createCrudValidation($offer, $ownershipContext)
            ->validateThat('id', $offer->id)
                ->isBlank()
            ->validateThat('authId', $offer->authId)
                ->isNotBlank()
                ->isInt()
            ->validateThat('orderContextId', $offer->orderContextId)
                ->isNotBlank()
                ->isNumeric()
            ->validateThat('listId', $offer->orderContextId)
                ->isNotBlank()
                ->isNumeric()
            ->getValidator($this->validator);
    }

    /**
     * @param OfferEntity $offer
     * @param OwnershipContext $ownershipContext
     * @return Validator
     */
    public function createUpdateValidation(OfferEntity $offer, OwnershipContext $ownershipContext): Validator
    {
        return $this->createCrudValidation($offer, $ownershipContext)
            ->validateThat('id', $offer->id)
                ->isNotBlank()
            ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param OfferEntity $offer
     * @param OwnershipContext $ownershipContext
     * @return ValidationBuilder
     */
    protected function createCrudValidation(OfferEntity $offer, OwnershipContext $ownershipContext): ValidationBuilder
    {
        return $this->validationBuilder
            ->validateThat('currencyFactor', $offer->currencyFactor)
                ->isNumeric()
            ->validateThat('discountValueNet', $offer->discountValueNet)
                ->isNumeric()
                ->isGreaterThan(0)
                ->withCallback(
                    function ($value) use ($offer, $ownershipContext): bool {
                        $references = $this->referenceRepository->fetchAllForList($offer->listId, $ownershipContext);

                        $amount = 0;
                        foreach ($references as $reference) {
                            $amount += ($reference->discountAmountNet / $reference->discountCurrencyFactor) * $reference->quantity;
                        }

                        $discount = ($value / $offer->currencyFactor);

                        return $discount <= $amount;
                    },
                    'Discount can not be greater than the sum of reference discount amounts',
                    self::DISCOUNT_GREATER_THAN_AMOUNT
                );
    }
}
