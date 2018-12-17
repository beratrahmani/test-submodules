<?php declare(strict_types = 1);

namespace Shopware\B2B\LineItemList\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LineItemReferenceValidationService
{
    const CAUSE_IS_PRODUCT = 'isProduct';
    const CAUSE_MAX_PURCHASE = 'MaxPurchase';
    const CAUSE_MIN_PURCHASE = 'MinPurchase';
    const CAUSE_PURCHASE_STEP = 'PurchaseStep';

    /**
     * @var ValidationBuilder
     */
    private $validationBuilder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ProductProviderInterface
     */
    private $productProvider;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param ProductProviderInterface $productProvider
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        ProductProviderInterface $productProvider
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->productProvider = $productProvider;
    }

    /**
     * @param LineItemReference $lineItemReference
     * @return Validator
     */
    public function createReferenceValidation(LineItemReference $lineItemReference): Validator
    {
        $validation = $this->createCrudValidation($lineItemReference)
            ->validateThat('id', $lineItemReference->id)
            ->isBlank()

            ->validateThat('referenceNumber', $lineItemReference->referenceNumber)
            ->isNotBlank()
            ->withCallback(function ($value = null) {
                return $this->productProvider
                    ->isProduct((string) $value);
            }, 'Missing product %value%', self::CAUSE_IS_PRODUCT)

            ->getValidator($this->validator);

        return $validation;
    }

    /**
     * @param LineItemReference $lineItemReference
     * @param int $listId
     * @return Validator
     */
    public function createInsertValidation(LineItemReference $lineItemReference, int $listId): Validator
    {
        return $this->createCrudValidation($lineItemReference)
            ->validateThat('id', $lineItemReference->id)
            ->isBlank()

            ->validateThat('referenceNumber', $lineItemReference->referenceNumber)
            ->isNotBlank()
            ->withCallback(function ($value = null) {
                return $this->productProvider
                    ->isProduct((string) $value);
            }, 'Missing product %value%', self::CAUSE_IS_PRODUCT)

            ->getValidator($this->validator);
    }

    /**
     * @param LineItemReference $lineItemReference
     * @return Validator
     */
    public function createUpdateValidation(LineItemReference $lineItemReference): Validator
    {
        return $this->createCrudValidation($lineItemReference)
            ->validateThat('id', $lineItemReference->id)
            ->isNotBlank()
            ->validateThat('referenceNumber', $lineItemReference->referenceNumber)
            ->isNotBlank()
            ->withCallback(function ($value = null) {
                return $this->productProvider
                    ->isProduct((string) $value);
            }, 'Missing product %value%', self::CAUSE_IS_PRODUCT)

            ->getValidator($this->validator);
    }

    /**
     * @param LineItemReference $lineItemReference
     * @return ValidationBuilder
     */
    public function createCrudValidation(LineItemReference $lineItemReference): ValidationBuilder
    {
        $this->productProvider->setMaxMinAndSteps($lineItemReference);

        return $this->validationBuilder
            ->validateThat('quantity', $lineItemReference->quantity)
            ->isNotBlank()
            ->isNumeric()
            ->withCallback(
                function ($value = null): bool {
                    return $value > 0;
                },
                'The value %value% must be greater than 0 for product %number%.',
                self::CAUSE_IS_PRODUCT,
                [
                    '%number%' => $lineItemReference->referenceNumber,
                ],
                true
            )
            ->withCallback(
                function ($value = null) use ($lineItemReference) {
                    if (!$lineItemReference->maxPurchase) {
                        return true;
                    }

                    return $value <= $lineItemReference->maxPurchase;
                },
                'This %value% must be lower or equal to the maximum order %int% for product %number%.',
                self::CAUSE_MAX_PURCHASE,
                [
                    '%number%' => $lineItemReference->referenceNumber,
                    '%int%' => $lineItemReference->maxPurchase,
                ]
            )
            ->withCallback(
                function ($value = null) use ($lineItemReference) {
                    return $value >= $lineItemReference->minPurchase;
                },
                'This %value% must be greater or equal to the minimum order %int% for product %number%.',
                self::CAUSE_MIN_PURCHASE,
                [
                    '%number%' => $lineItemReference->referenceNumber,
                    '%int%' => $lineItemReference->minPurchase,
                ]
            )
            ->withCallback(
                function ($value = null) use ($lineItemReference) {
                    return !(($value - $lineItemReference->minPurchase) % $lineItemReference->purchaseStep);
                },
                'The value %value% of the product %number% must be a multiple of %int% with a minimum order of %min%.',
                self::CAUSE_PURCHASE_STEP,
                [
                    '%number%' => $lineItemReference->referenceNumber,
                    '%int%' => $lineItemReference->purchaseStep,
                    '%min%' => $lineItemReference->minPurchase,

                ]
            )

            ->validateThat('comment', $lineItemReference->comment)
            ->isString();
    }
}
