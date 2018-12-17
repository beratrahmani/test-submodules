<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\TimeRestrictionType;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleService;
use Shopware\B2B\ContingentRule\Framework\ContingentRuleTypeValidationExtender;

class TimeRestrictionRuleValidationExtender implements ContingentRuleTypeValidationExtender
{
    /**
     * @var TimeRestrictionRuleEntity
     */
    private $timeRestrictionRuleEntity;

    /**
     * @var ContingentRuleService
     */
    private $contingentRuleService;

    /**
     * @param TimeRestrictionRuleEntity $timeRestrictionRuleEntity
     * @param ContingentRuleService $contingentRuleService
     */
    public function __construct(
        TimeRestrictionRuleEntity $timeRestrictionRuleEntity,
        ContingentRuleService $contingentRuleService
    ) {
        $this->timeRestrictionRuleEntity = $timeRestrictionRuleEntity;
        $this->contingentRuleService = $contingentRuleService;
    }

    /**
     * @param ValidationBuilder $validationBuilder
     * @return ValidationBuilder
     */
    public function extendValidator(ValidationBuilder $validationBuilder): ValidationBuilder
    {
        return $validationBuilder

            ->validateThat('timeRestriction', $this->timeRestrictionRuleEntity->timeRestriction)
            ->isNotBlank()
            ->isInArray($this->contingentRuleService->getTimeRestrictions())

            ->validateThat('value', $this->timeRestrictionRuleEntity->value)
            ->isNotBlank()
            ->isGreaterThan(0);
    }
}
