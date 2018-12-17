<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\CategoryType;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeValidationExtender;

class CategoryRuleValidationExtender implements ContingentRuleTypeValidationExtender
{
    /**
     * @var CategoryRuleEntity
     */
    private $categoryRuleEntity;

    /**
     * @param CategoryRuleEntity $categoryRuleEntity
     */
    public function __construct(CategoryRuleEntity $categoryRuleEntity)
    {
        $this->categoryRuleEntity = $categoryRuleEntity;
    }

    /**
     * @param ValidationBuilder $validationBuilder
     * @return ValidationBuilder
     */
    public function extendValidator(ValidationBuilder $validationBuilder): ValidationBuilder
    {
        return $validationBuilder
            ->validateThat('category', $this->categoryRuleEntity->categoryId)
            ->isNotBlank();
    }
}
