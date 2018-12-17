<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContingentRuleValidationService
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
     * @var ContingentRuleTypeFactory
     */
    private $typeFactory;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param ContingentRuleTypeFactory $typeFactory
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        ContingentRuleTypeFactory $typeFactory
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->typeFactory = $typeFactory;
    }

    /**
     * @param ContingentRuleEntity $contingentRule
     * @throws \InvalidArgumentException
     * @return Validator
     */
    public function createInsertValidation(ContingentRuleEntity $contingentRule): Validator
    {
        return $this->createCrudValidation($contingentRule)
            ->validateThat('id', $contingentRule->id)
            ->isBlank()
            ->validateThat('contingentGroupId', $contingentRule->contingentGroupId)
            ->isNotBlank()

            ->getValidator($this->validator);
    }

    /**
     * @param ContingentRuleEntity $contingentRule
     * @throws \InvalidArgumentException
     * @return Validator
     */
    public function createUpdateValidation(ContingentRuleEntity $contingentRule): Validator
    {
        return $this->createCrudValidation($contingentRule)
            ->validateThat('id', $contingentRule->id)
            ->isInt()

            ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param ContingentRuleEntity $contingentRule
     * @throws \InvalidArgumentException
     * @return ValidationBuilder
     */
    protected function createCrudValidation(ContingentRuleEntity $contingentRule): ValidationBuilder
    {
        $baseValidations = $this->validationBuilder

            ->validateThat('type', $contingentRule->type)
                ->isNotBlank();

        return $this->typeFactory
            ->findTypeByName($contingentRule->type)
            ->createValidationExtender($contingentRule)
            ->extendValidator($baseValidations);
    }
}
