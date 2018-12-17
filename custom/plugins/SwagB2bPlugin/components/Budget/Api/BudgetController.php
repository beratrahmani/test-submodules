<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Api;

use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Acl\Framework\AclGrantContextProviderChain;
use Shopware\B2B\Budget\Framework\BudgetCrudService;
use Shopware\B2B\Budget\Framework\BudgetEntity;
use Shopware\B2B\Budget\Framework\BudgetRepository;
use Shopware\B2B\Budget\Framework\BudgetSearchStruct;
use Shopware\B2B\Budget\Framework\BudgetService;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\StoreFrontAuthentication\Framework\Identity;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class BudgetController
{
    /**
     * @var GridHelper
     */
    private $requestHelper;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $debtorRepository;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var BudgetRepository
     */
    private $budgetRepository;

    /**
     * @var BudgetCrudService
     */
    private $budgetCrudService;

    /**
     * @var BudgetService
     */
    private $budgetService;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var AclGrantContextProviderChain
     */
    private $grantContextProviderChain;

    /**
     * @param BudgetRepository $budgetRepository
     * @param GridHelper $requestHelper
     * @param BudgetCrudService $budgetCrudService
     * @param DebtorAuthenticationIdentityLoader $debtorRepository
     * @param LoginContextService $loginContextService
     * @param BudgetService $budgetService
     * @param CurrencyService $currencyService
     * @param AclGrantContextProviderChain $grantContextProviderChain
     */
    public function __construct(
        BudgetRepository $budgetRepository,
        GridHelper $requestHelper,
        BudgetCrudService $budgetCrudService,
        DebtorAuthenticationIdentityLoader $debtorRepository,
        LoginContextService $loginContextService,
        BudgetService $budgetService,
        CurrencyService $currencyService,
        AclGrantContextProviderChain $grantContextProviderChain
    ) {
        $this->requestHelper = $requestHelper;
        $this->debtorRepository = $debtorRepository;
        $this->loginContextService = $loginContextService;
        $this->budgetRepository = $budgetRepository;
        $this->budgetCrudService = $budgetCrudService;
        $this->budgetService = $budgetService;
        $this->currencyService = $currencyService;
        $this->grantContextProviderChain = $grantContextProviderChain;
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function getListAction(
        string $debtorEmail,
        Request $request
    ): array {
        $search = new BudgetSearchStruct();

        $currencyContext = $this->currencyService->createCurrencyContext();

        $this->requestHelper
            ->extractSearchDataInRestApi($request, $search);

        $ownerShipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $budgets = $this->budgetRepository->fetchList($ownerShipContext, $search, $currencyContext);

        $this->addCurrentStatusToBudget($budgets, $ownerShipContext);

        $totalCount = $this->budgetRepository
            ->fetchTotalCount($ownerShipContext, $search);

        return ['success' => true, 'budgets' => $budgets, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $budgetId
     * @return array
     */
    public function getAction(string $debtorEmail, int $budgetId): array
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $budget = $this->budgetRepository
            ->fetchOneById($budgetId, $currencyContext, $ownershipContext);

        $this->addCurrentStatusToBudget([$budget], $ownershipContext);

        return ['success' => true, 'budget' => $budget];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, Request $request): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $currencyContext = $this->currencyService->createCurrencyContext();

        $aclGrantContext = $this->extractGrantContext($request, $ownershipContext);

        $data = $request->getPost();

        $newRecord = $this->budgetCrudService
            ->createNewRecordRequest($data);

        $budget = $this->budgetCrudService
            ->create($newRecord, $ownershipContext, $currencyContext, $aclGrantContext);

        $this->addCurrentStatusToBudget([$budget], $ownershipContext);

        return ['success' => true, 'budget' => $budget];
    }

    /**
     * @param string $debtorEmail
     * @param int $budgetId
     * @param Request $request
     * @return array
     */
    public function updateAction(string $debtorEmail, int $budgetId, Request $request): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $data = $request->getPost();
        $data['id'] = $budgetId;

        $currencyContext = $this->currencyService->createCurrencyContext();

        $existingRecord = $this->budgetCrudService
            ->createExistingRecordRequest($data);

        $budget = $this->budgetCrudService
            ->update($existingRecord, $currencyContext, $ownershipContext);

        $this->addCurrentStatusToBudget([$budget], $ownershipContext);

        return ['success' => true, 'budget' => $budget];
    }

    /**
     * @param string $debtorEmail
     * @param int $budgetId
     * @return array
     */
    public function getCurrentStatusAction(string $debtorEmail, int $budgetId)
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $status = $this->budgetService->getBudgetStatus($budgetId, $currencyContext, $ownershipContext);

        return ['success' => true, 'status' => $status];
    }

    /**
     * @param string $debtorEmail
     * @param int $budgetId
     * @param string $date
     * @return array
     */
    public function getStatusAction(string $debtorEmail, int $budgetId, string $date)
    {
        $currencyContext = $this->currencyService->createCurrencyContext();
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);

        $status = $this->budgetService->getBudgetStatus(
            $budgetId,
            $currencyContext,
            $ownershipContext,
            \DateTime::createFromFormat('Y-m-d', $date)
        );

        return ['success' => true, 'status' => $status];
    }

    /**
     * @param string $debtorEmail
     * @param int $budgetId
     * @return array
     */
    public function removeAction(string $debtorEmail, int $budgetId): array
    {
        $ownershipContext = $this->getDebtorOwnershipContextByEmail($debtorEmail);
        $currencyContext = $this->currencyService->createCurrencyContext();

        $budget = $this->budgetCrudService
            ->remove($budgetId, $currencyContext, $ownershipContext);

        return ['success' => true, 'budget' => $budget];
    }

    /**
     * @internal
     * @param string $debtorEmail
     * @return OwnershipContext
     */
    protected function getDebtorOwnershipContextByEmail(string $debtorEmail): OwnershipContext
    {
        return $this->getDebtorIdentityByEmail($debtorEmail)->getOwnershipContext();
    }

    /**
     * @internal
     * @param string $debtorEmail
     * @return Identity
     */
    protected function getDebtorIdentityByEmail(string $debtorEmail): Identity
    {
        return $this->debtorRepository
            ->fetchIdentityByEmail(
                $debtorEmail,
                $this->loginContextService,
                true
            );
    }

    /**
     * @internal
     * @param BudgetEntity[] $budgets
     * @param OwnershipContext $ownershipContext
     */
    protected function addCurrentStatusToBudget(array $budgets, OwnershipContext $ownershipContext)
    {
        $currencyContext = $this->currencyService->createCurrencyContext();

        foreach ($budgets as $budget) {
            try {
                $budget->currentStatus = $this->budgetService->getBudgetStatus(
                    $budget->id,
                    $currencyContext,
                    $ownershipContext,
                    new \DateTime()
                );
            } catch (\DomainException $e) {
                //nth
            }
        }
    }

    /**
     * @internal
     * @param Request $request
     * @param OwnershipContext $ownershipContext
     * @throws \InvalidArgumentException
     * @return AclGrantContext
     */
    protected function extractGrantContext(Request $request, OwnershipContext $ownershipContext): AclGrantContext
    {
        $grantContextIdentifier = $request->requireParam('grantContextIdentifier');

        return $this->grantContextProviderChain->fetchOneByIdentifier($grantContextIdentifier, $ownershipContext);
    }
}
