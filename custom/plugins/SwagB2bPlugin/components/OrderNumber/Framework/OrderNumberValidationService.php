<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderNumberValidationService
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
     * @var OrderNumberRepositoryInterface
     */
    private $orderNumberRepository;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param OrderNumberRepositoryInterface $orderNumberRepository
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        OrderNumberRepositoryInterface $orderNumberRepository
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->orderNumberRepository = $orderNumberRepository;
    }

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return Validator
     */
    public function createInsertValidation(OrderNumberEntity $orderNumberEntity): Validator
    {
        return $this->createCrudValidation($orderNumberEntity)
            ->validateThat('id', $orderNumberEntity->id)
            ->isBlank()

            ->getValidator($this->validator);
    }

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return Validator
     */
    public function createUpdateValidation(OrderNumberEntity $orderNumberEntity): Validator
    {
        return $this->createCrudValidation($orderNumberEntity)
            ->validateThat('id', $orderNumberEntity->id)
            ->isNotBlank()

            ->getValidator($this->validator);
    }

    /**
     * @param OrderNumberFileEntity $orderNumberCsvEntity
     * @param OrderNumberFileEntity[] $orderNumberCsvEntities
     * @return Validator
     */
    public function createCsvImportValidation(OrderNumberFileEntity $orderNumberCsvEntity, array $orderNumberCsvEntities): Validator
    {
        return $this->validationBuilder
            ->validateThat('ordernumber', $orderNumberCsvEntity->orderNumber)
            ->withCallback(
                function ($value) {
                    return !empty($value);
                },
                'This value should not be blank Line %line%.',
                'notBlankLine',
                ['%line%' => $orderNumberCsvEntity->row],
                true
            )
            ->withCallback(
                function () use ($orderNumberCsvEntity, $orderNumberCsvEntities) {
                    foreach ($orderNumberCsvEntities as $internEntity) {
                        if (
                            $internEntity !== $orderNumberCsvEntity
                            && $internEntity->orderNumber === $orderNumberCsvEntity->orderNumber
                        ) {
                            return false;
                        }
                    }

                    return true;
                },
                'This value %value% is already used Line %line%.',
                'isUnique',
                ['%line%' => $orderNumberCsvEntity->row]
            )
            ->withCallback(
                function () use ($orderNumberCsvEntity) {
                    return $orderNumberCsvEntity->productDetailsId !== null;
                },
                'This value %value% is not available Line %line%.',
                'notFound',
                ['%line%' => $orderNumberCsvEntity->row]
            )

            ->validateThat('customOrderNumber', $orderNumberCsvEntity->customOrderNumber)
            ->withCallback(
                function ($value) {
                    return !empty($value);
                },
                'This value should not be blank Line %line%.',
                'notBlankLine',
                ['%line%' => $orderNumberCsvEntity->row],
                true
            )
            ->withCallback(
                function () use ($orderNumberCsvEntity) {
                    return !preg_match('/[^a-z_\-0-9]/i', $orderNumberCsvEntity->customOrderNumber);
                },
                'This value %value% must not contain special chars Line %line%',
                'specialChars',
                ['%line%' => $orderNumberCsvEntity->row]
            )
            ->withCallback(
                function () use ($orderNumberCsvEntity, $orderNumberCsvEntities) {
                    foreach ($orderNumberCsvEntities as $internEntity) {
                        if (
                            $internEntity !== $orderNumberCsvEntity
                            && $internEntity->customOrderNumber === $orderNumberCsvEntity->customOrderNumber
                        ) {
                            return false;
                        }
                    }

                    return true;
                },
                'This value %value% is already used Line %line%.',
                'isUnique',
                ['%line%' => $orderNumberCsvEntity->row]
            )
            ->withCallback(
                function () use ($orderNumberCsvEntity) {
                    return strlen($orderNumberCsvEntity->customOrderNumber) >= 3;
                },
                'This value %value% should be at minimum 3 characters long Line %line%.',
                'smallerThan3',
                ['%line%' => $orderNumberCsvEntity->row]
            )

            ->getValidator($this->validator);
    }

    /**
     * @param OrderNumberEntity $orderNumberEntity
     * @return ValidationBuilder
     * @internal
     */
    protected function createCrudValidation(OrderNumberEntity $orderNumberEntity): ValidationBuilder
    {
        return $this->validationBuilder
            ->validateThat('ordernumber', $orderNumberEntity->orderNumber)
            ->isUnique(
                function () use ($orderNumberEntity) {
                    return $this->orderNumberRepository->isOrderNumberUnique($orderNumberEntity);
                }
            )
            ->isNotBlank()
            ->withCallback(
                function () use ($orderNumberEntity) {
                    return null !== $orderNumberEntity->productDetailsId;
                },
                'This value %value% is not available',
                'notAvailable'
            )

            ->validateThat('customOrderNumber', $orderNumberEntity->customOrderNumber)
            ->withCallback(
                function () use ($orderNumberEntity) {
                    return !preg_match('/[^a-z_\-0-9]/i', $orderNumberEntity->customOrderNumber);
                },
                'This value %value% must not contain special chars',
                'specialChars',
                [],
                true
            )
            ->isUnique(
                function () use ($orderNumberEntity) {
                    return $this->orderNumberRepository->isCustomOrderNumberAvailable($orderNumberEntity);
                }
            )
            ->isNotBlank()
            ->withCallback(
                function () use ($orderNumberEntity) {
                    return strlen($orderNumberEntity->customOrderNumber) >= 3;
                },
                'This value %value% should be at minimum 3 characters long.',
                strlen($orderNumberEntity->customOrderNumber) . ' is smaller than 3.'
            )

            ->validateThat('owner', $orderNumberEntity->contextOwnerId)
            ->isNotBlank();
    }
}
