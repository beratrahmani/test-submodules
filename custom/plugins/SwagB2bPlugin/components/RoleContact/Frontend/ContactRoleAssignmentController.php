<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Frontend;

use Shopware\B2B\Common\Controller\EmptyForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\RoleContact\Framework\RoleContactAssignmentService;
use Shopware\B2B\RoleContact\Framework\RoleContactRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactRoleAssignmentController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var RoleContactRepository
     */
    private $roleContactRepository;

    /**
     * @var RoleContactAssignmentService
     */
    private $roleContactAssignmentService;

    /**
     * @var GridHelper
     */
    private $gridHelper;

    /**
     * @param AuthenticationService $authenticationService
     * @param RoleContactRepository $roleContactRepository
     * @param RoleContactAssignmentService $roleContactAssignmentService
     * @param GridHelper $gridHelper
     * @param RoleRepository $roleRepository
     */
    public function __construct(
        AuthenticationService $authenticationService,
        RoleContactRepository $roleContactRepository,
        RoleContactAssignmentService $roleContactAssignmentService,
        GridHelper $gridHelper
    ) {
        $this->authenticationService = $authenticationService;
        $this->roleContactRepository = $roleContactRepository;
        $this->roleContactAssignmentService = $roleContactAssignmentService;
        $this->gridHelper = $gridHelper;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $contactId = (int) $request->requireParam('contactId');

        return [
            'contactId' => $contactId,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function treeAction(Request $request): array
    {
        $parentId = (int) $request->getParam('parentId');
        $contactId = (int) $request->requireParam('contactId');

        $ownershipContext = $this->authenticationService
            ->getIdentity()->getOwnershipContext();

        if (!$parentId) {
            $roles = $this->roleContactRepository
                ->fetchRootRoleAssignmentsAndCheckForContactAssignment($contactId, $ownershipContext);
        } else {
            $roles = $this->roleContactRepository
                ->fetchChildrenAndCheckForContactAssignment($parentId, $contactId, $ownershipContext);
        }

        return [
            'contactId' => $contactId,
            'roles' => $roles,
        ];
    }

    /**
     * @param Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     * @return array
     */
    public function assignAction(Request $request)
    {
        $request->checkPost('index', ['contactId' => $request->requireParam('contactId')]);

        $contactId = (int) $request->requireParam('contactId');
        $roleId = (int) $request->requireParam('roleId');

        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        try {
            if ($request->getParam('allow', false)) {
                $this->roleContactAssignmentService
                    ->assign($ownershipContext, $roleId, $contactId);
            } else {
                $this->roleContactAssignmentService
                    ->removeAssignment($ownershipContext, $roleId, $contactId);
            }
        } catch (ValidationException $validationException) {
            $this->gridHelper->pushValidationException($validationException);

            return $this->gridHelper->getValidationResponse('role');
        }

        throw new EmptyForwardException();
    }
}
