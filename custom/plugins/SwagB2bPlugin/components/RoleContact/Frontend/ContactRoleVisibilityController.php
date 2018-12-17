<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class ContactRoleVisibilityController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AclRepository
     */
    private $aclRepository;

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
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param AclRepository $aclRepository
     * @param RoleRepository $roleRepository
     * @param ContactRepository $contactRepository
     * @param GridHelper $gridHelper
     * @param AclAccessExtensionService $aclAccessExtensionService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AclRepository $aclRepository,
        RoleRepository $roleRepository,
        ContactRepository $contactRepository,
        GridHelper $gridHelper,
        AclAccessExtensionService $aclAccessExtensionService
    ) {
        $this->authenticationService = $authenticationService;
        $this->aclRepository = $aclRepository;
        $this->roleRepository = $roleRepository;
        $this->gridHelper = $gridHelper;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
        $this->contactRepository = $contactRepository;
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
    public function treeAction(Request $request)
    {
        $parentId = (int) $request->getParam('parentId');
        $contactId = (int) $request->requireParam('contactId');

        $ownershipContext = $this->authenticationService
            ->getIdentity()->getOwnershipContext();

        $contact = $this->contactRepository->fetchOneById($contactId, $ownershipContext);

        if (!$parentId) {
            $roles = $this->roleRepository
                ->fetchAclRootRoles($ownershipContext, false);
        } else {
            $roles = $this->roleRepository
                ->fetchChildren($parentId, $ownershipContext);
        }

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->aclRepository, $contact, $roles);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->aclRepository, $ownershipContext, $roles);

        return [
            'contactId' => $contactId,
            'roles' => $roles,
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function assignAction(Request $request)
    {
        $request->checkPost('index', ['contactId' => $request->getParam('contactId')]);

        $roleId = (int) $request->requireParam('roleId');
        $contactId = (int) $request->requireParam('contactId');
        $grantable = (bool) $request->getParam('grantable', false);
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $contact = $this->contactRepository->fetchOneById($contactId, $ownershipContext);

        if ($request->getParam('allow', false)) {
            $this->aclRepository->allow($contact, $roleId, $grantable);
        } else {
            $this->aclRepository->deny($contact, $roleId);
        }

        throw new B2bControllerForwardException('index', null, null, ['contactId' => $contactId]);
    }
}
