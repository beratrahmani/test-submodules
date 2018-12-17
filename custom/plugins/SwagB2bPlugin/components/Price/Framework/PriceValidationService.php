<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceValidationService
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
     * @var PriceRepository
     */
    private $priceRepository;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param PriceRepository $priceRepository
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        PriceRepository $priceRepository
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->priceRepository = $priceRepository;
    }

    /**
     * @param PriceEntity $price
     * @return Validator
     */
    public function createInsertValidation(PriceEntity $price): Validator
    {
        return $this->createCrudValidation($price)
            ->validateThat('id', $price->id)
            ->isBlank()
            ->getValidator($this->validator);
    }

    /**
     * @param PriceEntity $price
     * @return Validator
     */
    public function createUpdateValidation(PriceEntity $price): Validator
    {
        return $this->createCrudValidation($price)
                ->validateThat('id', $price->id)
                    ->isNotBlank()

                ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param PriceEntity $price
     * @return ValidationBuilder
     */
    protected function createCrudValidation(PriceEntity $price): ValidationBuilder
    {
        return $this->validationBuilder
            ->validateThat('articlesDetailsId', $price->articlesDetailsId)
                ->isNotBlank()
                ->isInt()
            ->validateThat('debtorId', $price->debtorId)
                ->isNotBlank()
                ->isInt()
            ->validateThat('to', $price->to)
                ->isNotBlank()
                ->isInt()
                ->isGreaterThan(0)
                ->isUnique(function () use ($price) {
                    return $this->priceRepository->checkForUniquePriceToRange($price);
                })
            ->validateThat('from', $price->from)
                ->isNotBlank()
                ->isInt()
                ->isGreaterThan(0)
                ->isUnique(function () use ($price) {
                    return $this->priceRepository->checkForUniquePriceToRange($price);
                })
            ->validateThat('price', $price->price)
                ->isNotBlank()
                ->isNumeric();
    }
}
