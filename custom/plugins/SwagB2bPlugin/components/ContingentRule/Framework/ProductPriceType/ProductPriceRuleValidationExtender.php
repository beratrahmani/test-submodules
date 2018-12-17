<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\ProductPriceType;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeValidationExtender;

class ProductPriceRuleValidationExtender implements ContingentRuleTypeValidationExtender
{
    /**
     * @var ProductPriceRuleEntity
     */
    private $productPriceRuleEntity;

    /**
     * @param ProductPriceRuleEntity $productPriceRuleEntity
     */
    public function __construct(ProductPriceRuleEntity $productPriceRuleEntity)
    {
        $this->productPriceRuleEntity = $productPriceRuleEntity;
    }

    /**
     * @param ValidationBuilder $validationBuilder
     * @return ValidationBuilder
     */
    public function extendValidator(ValidationBuilder $validationBuilder): ValidationBuilder
    {
        return $validationBuilder
            ->validateThat('productPrice', $this->productPriceRuleEntity->productPrice)
            ->isNotBlank();
    }
}
