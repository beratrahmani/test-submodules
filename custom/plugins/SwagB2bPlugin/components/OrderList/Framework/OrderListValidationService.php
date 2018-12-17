<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderListValidationService
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
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
    }

    /**
     * @param OrderListEntity $orderList
     * @return Validator
     */
    public function createInsertValidation(OrderListEntity $orderList): Validator
    {
        return $this->createCrudValidation($orderList)
            ->validateThat('id', $orderList->id)
            ->isBlank()

            ->getValidator($this->validator);
    }

    /**
     * @param OrderListEntity $orderList
     * @return Validator
     */
    public function createUpdateValidation(OrderListEntity $orderList): Validator
    {
        return $this->createCrudValidation($orderList)
            ->validateThat('id', $orderList->id)
            ->isInt()

            ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param OrderListEntity $orderList
     * @return ValidationBuilder
     */
    protected function createCrudValidation(OrderListEntity $orderList): ValidationBuilder
    {
        return $this->validationBuilder

            ->validateThat('name', $orderList->name)
                ->isNotBlank()
                ->isString()

            ->validateThat('listId', $orderList->listId)
                ->isNotBlank()
                ->isNumeric()

            ->validateThat('budgetId', $orderList->budgetId)
                ->isNumeric()

            ->validateThat('contextOwnerId', $orderList->contextOwnerId)
                ->isNotBlank();
    }
}
