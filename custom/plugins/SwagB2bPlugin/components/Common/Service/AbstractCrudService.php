<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Service;

use Shopware\B2B\Common\Entity;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Common\Validator\Validator;

abstract class AbstractCrudService
{
    /**
     * @param Entity $entity
     * @param Validator $validator
     */
    protected function testValidation(Entity $entity, Validator $validator)
    {
        $violations = $validator->getViolations();

        if (count($violations)) {
            throw new ValidationException($entity, $violations, 'Validation violations detected, can not proceed:', 400);
        }
    }
}
