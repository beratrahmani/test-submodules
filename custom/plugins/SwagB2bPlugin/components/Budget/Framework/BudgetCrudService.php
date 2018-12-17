<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework;

use Shopware\B2B\Acl\Framework\AclAccessWriterInterface;
use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class BudgetCrudService extends AbstractCrudService
{
    /**
     * @var BudgetRepository
     */
    private $budgetRepository;

    /**
     * @var BudgetValidationService
     */
    private $validationService;

    /**
     * @var AclAccessWriterInterface
     */
    private $aclAccessWriter;

    /**
     * @param BudgetRepository $budgetRepository
     * @param BudgetValidationService $validationService
     * @param AclAccessWriterInterface $aclAccessWriter
     */
    public function __construct(
        BudgetRepository $budgetRepository,
        BudgetValidationService $validationService,
        AclAccessWriterInterface $aclAccessWriter
    ) {
        $this->budgetRepository = $budgetRepository;
        $this->validationService = $validationService;
        $this->aclAccessWriter = $aclAccessWriter;
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createNewRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'identifier',
                'name',
                'ownerId',
                'notifyAuthor',
                'notifyAuthorPercentage',
                'active',
                'amount',
                'refreshType',
                'fiscalYear',
                'currencyFactor',
            ]
        );
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createExistingRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'id',
                'identifier',
                'name',
                'ownerId',
                'notifyAuthor',
                'notifyAuthorPercentage',
                'active',
                'amount',
                'refreshType',
                'fiscalYear',
                'currencyFactor',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @param CurrencyContext $currencyContext
     * @param AclGrantContext $grantContext
     * @return BudgetEntity
     */
    public function create(
        CrudServiceRequest $request,
        OwnershipContext $ownershipContext,
        CurrencyContext $currencyContext,
        AclGrantContext $grantContext
    ): BudgetEntity {
        $data = $request->getFilteredData();

        $budget = new BudgetEntity();

        $budget->setData($data);
        $budget->currencyFactor = $currencyContext->currentCurrencyFactor;

        $validation = $this->validationService
            ->createInsertValidation($budget, $ownershipContext);

        $this->testValidation($budget, $validation);

        $budget = $this->budgetRepository
            ->addBudget($budget, $ownershipContext);

        $this->aclAccessWriter->addNewSubject(
            $ownershipContext,
            $grantContext,
            $budget->id,
            true
        );

        return $budget;
    }

    /**
     * @param CrudServiceRequest $request
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return BudgetEntity
     */
    public function update(CrudServiceRequest $request, CurrencyContext $currencyContext, OwnershipContext $ownershipContext): BudgetEntity
    {
        $data = $request->getFilteredData();

        $currentBudget = $this->budgetRepository
            ->fetchOneById((int) $request->requireParam('id'), $currencyContext, $ownershipContext);

        $updatedBudget = new BudgetEntity();
        $updatedBudget->setData($data);

        if (abs($currentBudget->amount - $updatedBudget->amount) > 0.001) {
            $updatedBudget->currencyFactor = $currencyContext->currentCurrencyFactor;
        }

        $this->aclAccessWriter->testUpdateAllowed($ownershipContext, $updatedBudget->id);

        $validation = $this->validationService
            ->createUpdateValidation($updatedBudget, $ownershipContext);

        $this->testValidation($updatedBudget, $validation);

        $this->budgetRepository
            ->updateBudget($updatedBudget, $ownershipContext);

        return $updatedBudget;
    }

    /**
     * @param int $id
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     * @return BudgetEntity
     */
    public function remove(int $id, CurrencyContext $currencyContext, OwnershipContext $ownershipContext): BudgetEntity
    {
        $this->aclAccessWriter->testUpdateAllowed($ownershipContext, $id);

        $budget = $this->budgetRepository->fetchOneById($id, $currencyContext, $ownershipContext);

        $this->budgetRepository
            ->removeBudget($budget, $ownershipContext);

        return $budget;
    }
}
