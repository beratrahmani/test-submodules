<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework;

use Shopware\B2B\Address\Bridge\ConfigService;
use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddressValidationService
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
     * @var ConfigService
     */
    private $configService;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param ConfigService $configService
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        ConfigService $configService
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->configService = $configService;
    }

    /**
     * @param AddressEntity $address
     * @return Validator
     */
    public function createInsertValidation(AddressEntity $address): Validator
    {
        return $this->createCrudValidation($address)
            ->validateThat('id', $address->id)
            ->isBlank()

            ->getValidator($this->validator);
    }

    /**
     * @param AddressEntity $address
     * @return Validator
     */
    public function createUpdateValidation(AddressEntity $address): Validator
    {
        return $this->createCrudValidation($address)
            ->validateThat('id', $address->id)
            ->isInt()

            ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param AddressEntity $address
     * @return ValidationBuilder
     */
    protected function createCrudValidation(AddressEntity $address): ValidationBuilder
    {
        $requiredFields = $this->configService->getRequiredFieldsByAddress($address);

        $validation = $this->validationBuilder

            ->validateThat('salutation', $address->salutation)
            ->isNotBlank()

            ->validateThat('firstName', $address->firstname)
            ->isNotBlank()

            ->validateThat('lastName', $address->lastname)
            ->isNotBlank()

            ->validateThat('company', $address->company)
            ->isNotBlank()

            ->validateThat('country', $address->country_id)
            ->isNotBlank()

            ->validateThat('street', $address->street)
            ->isNotBlank()

            ->validateThat('zipcode', $address->zipcode)
            ->isNotBlank()

            ->validateThat('city', $address->city)
            ->isNotBlank();

        foreach ($requiredFields as $key => $field) {
            $validation->validateThat($key, $field)
                ->isNotBlank();
        }

        return $validation;
    }
}
