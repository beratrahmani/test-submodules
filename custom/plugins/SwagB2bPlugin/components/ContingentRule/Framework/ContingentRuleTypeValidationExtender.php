<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;

interface ContingentRuleTypeValidationExtender
{
    /**
     * @param ValidationBuilder $validationBuilder
     * @return ValidationBuilder
     */
    public function extendValidator(ValidationBuilder $validationBuilder): ValidationBuilder;
}
