<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Frontend;

use Shopware\B2B\Acl\Framework\AclGrantContextProviderChain;
use Shopware\B2B\Budget\Framework\BudgetCrudService;
use Shopware\B2B\Budget\Framework\BudgetEntity;
use Shopware\B2B\Budget\Framework\BudgetRepository;
use Shopware\B2B\Budget\Framework\BudgetSearchStruct;
use Shopware\B2B\Budget\Framework\BudgetService;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\EmptyForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Company\Frontend\CompanyFilterResolver;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Currency\Framework\CurrencyContext;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class BudgetController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var GridHelper
     */
    private $budgetGridHelper;

    /**
     * @var BudgetRepository
     */
    private $budgetRepository;

    /**
     * @var BudgetCrudService
     */
    private $crudService;

    /**
     * @var BudgetService
     */
    private $service;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var CompanyFilterResolver
     */
    private $companyFilterResolver;

    /**
     * @var AclGrantContextProviderChain
     */
    private $grantContextProviderChain;

    /**
     * @param AuthenticationService $authenticationService
     * @param GridHelper $gridHelper
     * @param BudgetRepository $budgetRepository
     * @param BudgetCrudService $crudService
     * @param BudgetService $service
     * @param CurrencyService $currencyService
     * @param ContactRepository $contactRepository
     * @param CompanyFilterResolver $companyFilterResolver
     * @param AclGrantContextProviderChain $grantContextProviderChain
     */
    public function __construct(
        AuthenticationService $authenticationService,
        GridHelper $gridHelper,
        BudgetRepository $budgetRepository,
        BudgetCrudService $crudService,
        BudgetService $service,
        CurrencyService $currencyService,
        ContactRepository $contactRepository,
        CompanyFilterResolver $companyFilterResolver,
        AclGrantContextProviderChain $grantContextProviderChain
    ) {
        $this->authenticationService = $authenticationService;
        $this->budgetGridHelper = $gridHelper;
        $this->budgetRepository = $budgetRepository;
        $this->crudService = $crudService;
        $this->service = $service;
        $this->currencyService = $currencyService;
        $this->contactRepository = $contactRepository;
        $this->companyFilterResolver = $companyFilterResolver;
        $this->grantContextProviderChain = $grantContextProviderChain;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $searchStruct = $this->createSearchStruct($request, $ownershipContext);

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $budgets = $this->budgetRepository
            ->fetchList($ownershipContext, $searchStruct, $currencyContext);

        $this->addCurrentStatusToBudget($budgets, $currencyContext, $ownershipContext);

        $totalCount = $this->budgetRepository
            ->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->budgetGridHelper->getMaxPage($totalCount);
        $currentPage = $this->budgetGridHelper->getCurrentPage($request);

        $budgetGrid = $this->budgetGridHelper
            ->getGridState($request, $searchStruct, $budgets, $maxPage, $currentPage);

        return array_merge(
            [
                'budgetGrid' => $budgetGrid,
                'grantContext' => $searchStruct->aclGrantContext->getIdentifier(),
            ],
            $this->companyFilterResolver->getViewFilterVariables($searchStruct)
        );
    }

    /**
     * @internal
     * @param BudgetEntity[] $budgets
     * @param CurrencyContext $currencyContext
     * @param OwnershipContext $ownershipContext
     */
    protected function addCurrentStatusToBudget(
        array $budgets,
        CurrencyContext $currencyContext,
        OwnershipContext $ownershipContext
    ) {
        foreach ($budgets as $budget) {
            $budget->currentStatus = $this->service->getBudgetStatus($budget->id, $currencyContext, $ownershipContext, new \DateTime());
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function detailAction(Request $request): array
    {
        $budgetId = (int) $request->requireParam('id');
        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $budget = $this->budgetRepository
            ->fetchOneById($budgetId, $currencyContext, $ownershipContext);

        $validationResponse = $this->budgetGridHelper
            ->getValidationResponse('budget');

        $debtorAuthId = $ownershipContext->contextOwnerId;

        return array_merge([
            'budget' => $budget,
            'contacts' => $this->contactRepository->fetchFullList($ownershipContext),
            'debtor' => $this->authenticationService->getIdentityByAuthId($debtorAuthId)->getEntity(),
            'debtorAuthId' => $debtorAuthId,
        ], $validationResponse);
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function updateAction(Request $request)
    {
        $request->checkPost();
        $ownershipContext = $this->authenticationService
        ->getIdentity()
        ->getOwnershipContext();


        $post = $request->getPost();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $serviceRequest = $this->crudService
            ->createExistingRecordRequest($post);

        try {
            $this->crudService->update($serviceRequest, $currencyContext, $ownershipContext);
        } catch (ValidationException $e) {
            $this->budgetGridHelper->pushValidationException($e);
        }

        throw new B2bControllerForwardException('detail', null, null, ['id' => $post['id']]);
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function removeAction(Request $request)
    {
        $request->checkPost();
        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $id = (int) $request->requireParam('id');

        $this->crudService
            ->remove($id, $currencyContext, $ownershipContext);

        throw new EmptyForwardException();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newAction(Request $request): array
    {
        $validationResponse = $this->budgetGridHelper
            ->getValidationResponse('budget');

        $ownershipContext = $this->authenticationService
            ->getIdentity()
            ->getOwnershipContext();

        $debtorAuthId = $ownershipContext->contextOwnerId;

        return array_merge([
            'isNew' => true,
            'contacts' => $this->contactRepository->fetchFullList($ownershipContext),
            'debtor' => $this->authenticationService->getIdentityByAuthId($debtorAuthId)->getEntity(),
            'debtorAuthId' => $debtorAuthId,
            'grantContext' => $request->requireParam('grantContext'),
        ], $validationResponse);
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function createAction(Request $request)
    {
        $request->checkPost();

        $post = $request->getPost();

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $identity = $this->authenticationService->getIdentity();

        $serviceRequest = $this->crudService
            ->createNewRecordRequest($post);

        $grantContext = $this->grantContextProviderChain
            ->fetchOneByIdentifier($request->requireParam('grantContext'), $identity->getOwnershipContext());

        try {
            $budget = $this->crudService
                ->create($serviceRequest, $identity->getOwnershipContext(), $currencyContext, $grantContext);
        } catch (ValidationException $e) {
            $this->budgetGridHelper->pushValidationException($e);
            throw new B2bControllerForwardException('new');
        }

        throw new B2bControllerForwardException('detail', null, null, ['id' => $budget->id]);
    }

    /**
     * @internal
     * @param Request $request
     * @param OwnershipContext $ownershipContext
     * @return BudgetSearchStruct
     */
    protected function createSearchStruct(Request $request, OwnershipContext $ownershipContext): BudgetSearchStruct
    {
        $searchStruct = new BudgetSearchStruct();

        $this->companyFilterResolver
            ->extractGrantContextFromRequest($request, $searchStruct, $ownershipContext);

        $this->budgetGridHelper
            ->extractSearchDataInStoreFront($request, $searchStruct);

        return $searchStruct;
    }
}
