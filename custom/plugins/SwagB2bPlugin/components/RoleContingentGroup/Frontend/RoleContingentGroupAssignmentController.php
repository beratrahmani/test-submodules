<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContingentGroup\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Controller\EmptyForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupRepository;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupSearchStruct;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\RoleContingentGroup\Framework\RoleContingentGroupAssignmentService;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class RoleContingentGroupAssignmentController
{
    /**
     * @var  AclRepository
     */
    private $roleContingentGroupAclRepository;

    /**
     * @var ContingentGroupRepository
     */
    private $contingentGroupRepository;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var RoleContingentGroupAssignmentService
     */
    private $assignmentService;

    /**
     * @param AuthenticationService $authenticationService
     * @param AclRepository $roleContingentGroupAclRepository
     * @param ContingentGroupRepository $contingentGroupRepository
     * @param GridHelper $gridHelper
     * @param AclAccessExtensionService $aclAccessExtensionService
     * @param RoleRepository $roleRepository
     * @param RoleContingentGroupAssignmentService $assignmentService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AclRepository $roleContingentGroupAclRepository,
        ContingentGroupRepository $contingentGroupRepository,
        GridHelper $gridHelper,
        AclAccessExtensionService $aclAccessExtensionService,
        RoleRepository $roleRepository,
        RoleContingentGroupAssignmentService $assignmentService
    ) {
        $this->roleContingentGroupAclRepository = $roleContingentGroupAclRepository;
        $this->contingentGroupRepository = $contingentGroupRepository;
        $this->gridHelper = $gridHelper;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
        $this->roleRepository = $roleRepository;
        $this->authenticationService = $authenticationService;
        $this->assignmentService = $assignmentService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $roleId = (int) $request->requireParam('roleId');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $searchStruct = new ContingentGroupSearchStruct();

        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $contingentGroups = $this->contingentGroupRepository
            ->fetchAllContingentGroupsWithCheckForRoleAssignment($ownershipContext, $searchStruct, $roleId);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->roleContingentGroupAclRepository, $role, $contingentGroups);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership(
                $this->roleContingentGroupAclRepository,
                $ownershipContext,
                $contingentGroups
            );

        $contingentGroupsCount = $this->contingentGroupRepository->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper->getMaxPage($contingentGroupsCount);

        $currentPage = $this->gridHelper->getCurrentPage($request);

        $gridState = $this->gridHelper
            ->getGridState($request, $searchStruct, $contingentGroups, $maxPage, $currentPage);

        return array_merge(
            [
                'roleId' => $roleId,
                'gridState' => $gridState,
            ],
            $this->gridHelper->getValidationResponse('role')
        );
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     * @return array
     */
    public function assignAction(Request $request): array
    {
        $request->checkPost('index', ['roleId' => $request->requireParam('roleId')]);
        $crudRequest = $this->assignmentService->createAssignRecordRequest($request->getPost());
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            if ($request->getParam('allow')) {
                $this->assignmentService->assign($crudRequest, $ownershipContext);
            } else {
                $this->assignmentService->removeAssignment($crudRequest, $ownershipContext);
            }
        } catch (ValidationException $validationException) {
            $this->gridHelper->pushValidationException($validationException);

            return $this->gridHelper->getValidationResponse('role');
        }

        throw new EmptyForwardException();
    }
}
