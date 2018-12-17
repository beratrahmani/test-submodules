<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Shopware\B2B\Common\Repository\MysqlRepository;
use Shopware\B2B\Common\Repository\NotFoundException;
use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BudgetValidationService
{
    const CAUSE_WRONG_OWNER = 'WRONG_OWNER';

    /**
     * @var ValidationBuilder
     */
    private $validationBuilder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        AuthenticationService $authenticationService
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param BudgetEntity $budget
     * @param OwnershipContext $ownershipContext
     * @return Validator
     */
    public function createInsertValidation(BudgetEntity $budget, OwnershipContext $ownershipContext): Validator
    {
        return $this->createCrudValidation($budget, $ownershipContext)
            ->validateThat('id', $budget->id)
                ->isBlank()

            ->getValidator($this->validator);
    }

    /**
     * @param BudgetEntity $budget
     * @param OwnershipContext $ownershipContext
     * @return Validator
     */
    public function createUpdateValidation(BudgetEntity $budget, OwnershipContext $ownershipContext): Validator
    {
        return $this->createCrudValidation($budget, $ownershipContext)
            ->validateThat('id', $budget->id)
                ->isNotBlank()

            ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param BudgetEntity $budget
     * @param OwnershipContext $ownershipContext
     * @return ValidationBuilder
     */
    protected function createCrudValidation(BudgetEntity $budget, OwnershipContext $ownershipContext): ValidationBuilder
    {
        return $this->validationBuilder

            ->validateThat('identifier', $budget->identifier)
                ->isNotBlank()
                ->isString()

            ->validateThat('name', $budget->name)
                ->isNotBlank()
                ->isString()

            ->validateThat('active', $budget->active)
                ->isBool()

            ->validateThat('amount', $budget->amount)
                ->isNotBlank()
                ->isNumeric()
                ->isGreaterThan(0, true)

            ->validateThat('ownerId', $budget->ownerId)
                ->isNumeric()
                ->withCallback(
                    function ($value = null) use ($ownershipContext) {
                        try {
                            $identity = $this->authenticationService->getIdentityByAuthId((int) $value);

                            return $identity->getOwnershipContext()->contextOwnerId === $ownershipContext->contextOwnerId;
                        } catch (NotFoundException $notFoundException) {
                            return false;
                        }
                    },
                    'The ownerid %value% does not belong to the current user',
                    self::CAUSE_WRONG_OWNER
                )

            ->validateThat('fiscalYear', $budget->fiscalYear)
                ->isNotBlank()
                ->isString()
                ->withCallback(
                    function ($value = null) {
                        $dateTime = \DateTime::createFromFormat(
                        MysqlRepository::MYSQL_DATE_FORMAT,
                        $value
                    );

                        if (!$dateTime || $dateTime->format(MysqlRepository::MYSQL_DATE_FORMAT) === '0000-00-00') {
                            return false;
                        }

                        return true;
                    },
                    'Wrong date format given %value%',
                    'BudgetDate'
                )

            ->validateThat('refreshType', $budget->refreshType)
                ->isNotBlank()
                ->isInArray([
                    BudgetService::TYPE_NONE,
                    BudgetService::TYPE_YEARLY,
                    BudgetService::TYPE_MONTHLY,
                    BudgetService::TYPE_QUARTERLY,
                    BudgetService::TYPE_BIANNUAL,
                ])

            ->validateThat('notifyAuthor', $budget->notifyAuthor)
                ->isBool()

            ->validateThat('notifyAuthorPercentage', $budget->notifyAuthorPercentage)
                ->isNumeric();
    }
}
