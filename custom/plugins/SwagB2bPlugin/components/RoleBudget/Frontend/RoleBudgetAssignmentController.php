<?php declare(strict_types=1);

namespace Shopware\B2B\RoleBudget\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Budget\Framework\BudgetRepository;
use Shopware\B2B\Budget\Framework\BudgetSearchStruct;
use Shopware\B2B\Common\Controller\EmptyForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\RoleBudget\Framework\RoleBudgetAssignmentService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class RoleBudgetAssignmentController
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var BudgetRepository
     */
    private $budgetRepository;

    /**
     * @var AclRepository
     */
    private $budgetAclRepository;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * @var RoleBudgetAssignmentService
     */
    private $assignmentService;

    /**
     * @param AuthenticationService $authenticationService
     * @param BudgetRepository $budgetRepository
     * @param AclRepository $budgetAclRepository
     * @param RoleRepository $roleRepository
     * @param GridHelper $gridHelper
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param CurrencyService $currencyService
     * @param RoleBudgetAssignmentService $assignmentService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        BudgetRepository $budgetRepository,
        AclRepository $budgetAclRepository,
        RoleRepository $roleRepository,
        GridHelper $gridHelper,
        AclAccessExtensionService $aclAccessExtensionService,
        CurrencyService $currencyService,
        RoleBudgetAssignmentService $assignmentService
    ) {
        $this->authenticationService = $authenticationService;
        $this->roleRepository = $roleRepository;
        $this->gridHelper = $gridHelper;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
        $this->budgetRepository = $budgetRepository;
        $this->budgetAclRepository = $budgetAclRepository;
        $this->currencyService = $currencyService;
        $this->assignmentService = $assignmentService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $roleId = (int) $request->requireParam('roleId');

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $searchStruct = new BudgetSearchStruct();

        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $budgets = $this->budgetRepository
            ->fetchList($ownershipContext, $searchStruct, $currencyContext);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->budgetAclRepository, $role, $budgets);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->budgetAclRepository, $ownershipContext, $budgets);

        $count = $this->budgetRepository
            ->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper->getMaxPage($count);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $budgets, $maxPage, $currentPage);

        return [
            'gridState' => $gridState,
            'budgets' => $budgets,
            'entity' => $role,
            'entityType' => 'role',
            'assignmentController' => 'b2brolebudget',
        ];
    }

    /**
     * @param  Request $request
     * @throws EmptyForwardException
     * @return array
     */
    public function assignAction(Request $request): array
    {
        $roleId = (int) $request->requireParam('roleId');
        $request->checkPost('index', ['roleId' => $roleId]);
        $crudRequest = $this->assignmentService->createAssignRecordRequest($request->getPost());
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            if ($request->getParam('allow', false)) {
                $this->assignmentService->allow($crudRequest, $ownershipContext);
            } else {
                $this->assignmentService->deny($crudRequest, $ownershipContext);
            }
        } catch (ValidationException $validationException) {
            $this->gridHelper->pushValidationException($validationException);

            return $this->gridHelper->getValidationResponse('role');
        }

        throw new EmptyForwardException();
    }
}
