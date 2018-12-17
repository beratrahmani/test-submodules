<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Contact\Framework\ContactRepository;
use Shopware\B2B\Contact\Framework\ContactSearchStruct;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class RoleContactVisibilityController
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AclRepository
     */
    private $contactAclRepository;

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
        $this->contactAclRepository = $aclRepository;
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
        $roleId = (int) $request->requireParam('roleId');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $role = $this->roleRepository
            ->fetchOneById($roleId, $ownershipContext);

        $searchStruct = new ContactSearchStruct();
        $this->gridHelper->extractSearchDataInStoreFront($request, $searchStruct);

        $contacts = $this->contactRepository
            ->fetchList($ownershipContext, $searchStruct);

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->contactAclRepository, $role, $contacts);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->contactAclRepository, $ownershipContext, $contacts);

        $count = $this->contactRepository
            ->fetchTotalCount($ownershipContext, $searchStruct);

        $maxPage = $this->gridHelper->getMaxPage($count);
        $currentPage = $this->gridHelper->getCurrentPage($request);

        return [
            'gridState' => $this->gridHelper->getGridState($request, $searchStruct, $contacts, $maxPage, $currentPage),
            'contacts' => $contacts,
            'role' => $role,
        ];
    }

    /**
     * @param Request $request
     * @throws B2bControllerForwardException
     */
    public function assignAction(Request $request)
    {
        $request->checkPost('index', ['roleId' => $request->getParam('roleId')]);

        $contactId = (int) $request->requireParam('contactId');
        $roleId = (int) $request->requireParam('roleId');
        $grantable = (bool) $request->getParam('grantable', false);
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $role = $this->roleRepository
            ->fetchOneById($roleId, $ownershipContext);

        if ($request->getParam('allow', false)) {
            $this->contactAclRepository
                ->allow($role, $contactId, $grantable);
        } else {
            $this->contactAclRepository
                ->deny($role, $contactId);
        }

        throw new B2bControllerForwardException('index', null, null, ['roleId' => $roleId]);
    }
}
