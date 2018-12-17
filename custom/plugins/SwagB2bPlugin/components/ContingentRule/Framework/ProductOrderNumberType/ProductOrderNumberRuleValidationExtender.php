<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\ProductOrderNumberType;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeValidationExtender;

class ProductOrderNumberRuleValidationExtender implements ContingentRuleTypeValidationExtender
{
    /**
     * @var ProductOrderNumberRuleEntity
     */
    private $productOrderNumberRuleEntity;

    /**
     * @param ProductOrderNumberRuleEntity $productOrderNumberRuleEntity
     */
    public function __construct(ProductOrderNumberRuleEntity $productOrderNumberRuleEntity)
    {
        $this->productOrderNumberRuleEntity = $productOrderNumberRuleEntity;
    }

    /**
     * @param ValidationBuilder $validationBuilder
     * @return ValidationBuilder
     */
    public function extendValidator(ValidationBuilder $validationBuilder): ValidationBuilder
    {
        return $validationBuilder
            ->validateThat('productOrderNumber', $this->productOrderNumberRuleEntity->productOrderNumber)
            ->isNotBlank();
    }
}
