<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Budget\Framework\BudgetRepository;
use Shopware\B2B\Budget\Framework\BudgetSearchStruct;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Currency\Framework\CurrencyService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactBudgetController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

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
     * @param AuthenticationService $authenticationService
     * @param BudgetRepository $budgetRepository
     * @param AclRepository $budgetAclRepository
     * @param ContactRepository $contactRepository
     * @param GridHelper $gridHelper
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param CurrencyService $currencyService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        BudgetRepository $budgetRepository,
        AclRepository $budgetAclRepository,
        ContactRepository $contactRepository,
        GridHelper $gridHelper,
        AclAccessExtensionService $aclAccessExtensionService,
        CurrencyService $currencyService
    ) {
        $this->authenticationService = $authenticationService;
        $this->contactRepository = $contactRepository;
        $this->gridHelper = $gridHelper;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
        $this->budgetRepository = $budgetRepository;
        $this->budgetAclRepository = $budgetAclRepository;
        $this->currencyService = $currencyService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $contactId = (int) $request->requireParam('contactId');
        $ownershipContext = $this->authenticationService->getIdentity()
            ->getOwnershipContext();

        $contact = $this->contactRepository
            ->fetchOneById($contactId, $ownershipContext);

        $searchStruct = new BudgetSearchStruct();
        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $currencyContext = $this->currencyService
            ->createCurrencyContext();

        $budgets = $this->budgetRepository
            ->fetchList($ownershipContext, $searchStruct, $currencyContext);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->budgetAclRepository, $contact, $budgets);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->budgetAclRepository, $ownershipContext, $budgets);

        $count = $this->budgetRepository
            ->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper->getMaxPage($count);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        return [
            'gridState' => $this->gridHelper
                ->getGridState($request, $searchStruct, $budgets, $maxPage, $currentPage),
            'budgets' => $budgets,
            'entity' => $contact,
            'entityType' => 'contact',
            'assignmentController' => 'b2bcontactbudget',
        ];
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function assignAction(Request $request)
    {
        $contactId = (int) $request->requireParam('contactId');
        $budgetId = (int) $request->requireParam('budgetId');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $contact = $this->contactRepository
            ->fetchOneById($contactId, $ownershipContext);

        if ($request->getParam('allow', false)) {
            $this->budgetAclRepository
                ->allow($contact, $budgetId, (bool) $request->getParam('grantable', false));
        } else {
            $this->budgetAclRepository->deny($contact, $budgetId);
        }

        throw new B2bControllerForwardException(
            'index',
            null,
            null,
            ['contactId' => $contactId]
        );
    }
}
