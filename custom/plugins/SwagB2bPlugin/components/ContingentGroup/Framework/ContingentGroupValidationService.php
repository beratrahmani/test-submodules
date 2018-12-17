<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContingentGroupValidationService
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
     * @param ContingentGroupEntity $contingentGroup
     * @return Validator
     */
    public function createInsertValidation(ContingentGroupEntity $contingentGroup): Validator
    {
        return $this->createCrudValidation($contingentGroup)
            ->validateThat('id', $contingentGroup->id)
            ->isBlank()
            ->getValidator($this->validator);
    }

    /**
     * @param ContingentGroupEntity $contingentGroup
     * @return Validator
     */
    public function createUpdateValidation(ContingentGroupEntity $contingentGroup): Validator
    {
        return $this->createCrudValidation($contingentGroup)
            ->validateThat('id', $contingentGroup->id)
            ->isNotBlank()
            ->isInt()

            ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param ContingentGroupEntity $contingentGroup
     * @return ValidationBuilder
     */
    protected function createCrudValidation(ContingentGroupEntity $contingentGroup): ValidationBuilder
    {
        return $this->validationBuilder

            ->validateThat('name', $contingentGroup->name)
            ->isNotBlank();
    }
}
