<?php declare(strict_types = 1);

namespace Shopware\B2B\Offer\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Shopware\B2B\LineItemList\Framework\LineItemReferenceValidationService;
use Shopware\B2B\LineItemList\Framework\ProductProviderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OfferLineItemReferenceValidationService
{
    /**
     * @var ProductProviderInterface
     */
    private $productProvider;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var OfferLineItemReferenceRepository
     */
    private $referenceRepository;

    /**
     * @var LineItemReferenceValidationService
     */
    private $lineItemReferenceValidationService;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param ProductProviderInterface $productProvider
     * @param OfferLineItemReferenceRepository $referenceRepository
     * @param LineItemReferenceValidationService $lineItemReferenceValidationService
     */
    public function __construct(
        ValidatorInterface $validator,
        ProductProviderInterface $productProvider,
        OfferLineItemReferenceRepository $referenceRepository,
        LineItemReferenceValidationService $lineItemReferenceValidationService
    ) {
        $this->productProvider = $productProvider;
        $this->validator = $validator;
        $this->referenceRepository = $referenceRepository;
        $this->lineItemReferenceValidationService = $lineItemReferenceValidationService;
    }

    /**
     * @internal
     * @param OfferLineItemReferenceEntity $lineItemReference
     * @return ValidationBuilder
     */
    protected function createCrudValidation(OfferLineItemReferenceEntity $lineItemReference): ValidationBuilder
    {
        return $this->lineItemReferenceValidationService->createCrudValidation($lineItemReference)
            ->validateThat('comment', $lineItemReference->comment)
                ->isString()
            ->validateThat('amountNet', $lineItemReference->amountNet)
                ->isNumeric()
            ->validateThat('amount', $lineItemReference->amount)
                ->isNumeric()
            ->validateThat('discountAmountNet', $lineItemReference->discountAmountNet)
                ->isNumeric()
                ->isGreaterEqualThan(0)
            ->validateThat('discountAmount', $lineItemReference->discountAmount)
                ->isNumeric()
                ->isGreaterEqualThan(0);
    }

    /**
     * @param OfferLineItemReferenceEntity $lineItemReference
     * @return Validator
     */
    public function createCrudValidator(OfferLineItemReferenceEntity $lineItemReference): Validator
    {
        return $this->createCrudValidation($lineItemReference)
            ->validateThat('referenceNumber', $lineItemReference->referenceNumber)
            ->isNotBlank()
            ->withCallback(function ($value = null) {
                return $this->productProvider
                    ->isProduct((string) $value);
            }, 'Missing product %value%', LineItemReferenceValidationService::CAUSE_IS_PRODUCT)->getValidator($this->validator);
    }

    /**
     * @param OfferLineItemReferenceEntity $lineItemReference
     * @param int $offerId
     * @return Validator
     */
    public function createUpdateValidation(OfferLineItemReferenceEntity $lineItemReference, int $offerId): Validator
    {
        return $this->createCrudValidation($lineItemReference)
            ->validateThat('id', $lineItemReference->id)
            ->isNotBlank()
            ->validateThat('referenceNumber', $lineItemReference->referenceNumber)
            ->isNotBlank()
            ->withCallback(function ($value = null) {
                return $this->productProvider
                    ->isProduct((string) $value);
            }, 'Missing product %value%', LineItemReferenceValidationService::CAUSE_IS_PRODUCT)
            ->getValidator($this->validator);
    }

    /**
     * @param OfferLineItemReferenceEntity $lineItemReference
     * @param int $listId
     * @return Validator
     */
    public function createInsertValidation(OfferLineItemReferenceEntity $lineItemReference, int $listId): Validator
    {
        $validation = $this->createCrudValidation($lineItemReference)
            ->validateThat('id', $lineItemReference->id)
            ->isBlank()
            ->validateThat('referenceNumber', $lineItemReference->referenceNumber)
            ->isNotBlank()
            ->isUnique(function () use ($lineItemReference, $listId) {
                return !$this->referenceRepository
                    ->hasReference($lineItemReference->referenceNumber, $listId);
            })
            ->withCallback(function ($value = null) {
                return $this->productProvider
                    ->isProduct((string) $value);
            }, 'Missing product %value%', LineItemReferenceValidationService::CAUSE_IS_PRODUCT)

            ->getValidator($this->validator);

        return $validation;
    }
}
