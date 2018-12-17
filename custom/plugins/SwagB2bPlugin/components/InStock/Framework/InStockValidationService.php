<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InStockValidationService
{
    /**
     * @var ValidationBuilder
     */
    private $validationBuilder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
    }

    /**
     * @param InStockEntity $entity
     * @return Validator
     */
    public function createInsertValidation(InStockEntity $entity): Validator
    {
        return $this->createCrudValidation($entity)
            ->validateThat('id', $entity->id)
                ->isBlank()
            ->getValidator($this->validator);
    }

    /**
     * @param InStockEntity $entity
     * @return Validator
     */
    public function createUpdateValidation(InStockEntity $entity): Validator
    {
        return $this->createCrudValidation($entity)
                ->validateThat('id', $entity->id)
                    ->isNotBlank()
                ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param InStockEntity $entity
     * @return ValidationBuilder
     */
    protected function createCrudValidation(InStockEntity $entity): ValidationBuilder
    {
        return $this->validationBuilder
            ->validateThat('articlesDetailsId', $entity->articlesDetailsId)
                ->isNotBlank()
                ->isInt()
            ->validateThat('authId', $entity->authId)
                ->isNotBlank()
                ->isInt()
            ->validateThat('inStock', $entity->inStock)
                ->isNotBlank()
                ->isInt();
    }
}
