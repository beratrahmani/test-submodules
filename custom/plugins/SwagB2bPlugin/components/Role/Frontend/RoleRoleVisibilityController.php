<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Frontend;

use Shopware\B2B\Acl\Framework\AclAccessExtensionService;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Controller\B2bControllerForwardException;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\AuthenticationService;

class RoleRoleVisibilityController
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var AclAccessExtensionService
     */
    private $aclAccessExtensionService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @param AuthenticationService $authenticationService
     * @param AclRepository $aclRepository
     * @param RoleRepository $roleRepository
     * @param AclAccessExtensionService $aclAccessExtensionService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AclRepository $aclRepository,
        RoleRepository $roleRepository,
        AclAccessExtensionService $aclAccessExtensionService
    ) {
        $this->authenticationService = $authenticationService;
        $this->roleRepository = $roleRepository;
        $this->aclAccessExtensionService = $aclAccessExtensionService;
        $this->aclRepository = $aclRepository;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request): array
    {
        $baseRoleId = (int) $request->requireParam('roleId');

        return [
            'baseRoleId' => $baseRoleId,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function treeAction(Request $request): array
    {
        $parentId = (int) $request->getParam('parentId');
        $baseRoleId = (int) $request->requireParam('baseRoleId');
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $baseRole = $this->roleRepository->fetchOneById($baseRoleId, $ownershipContext);

        if (!$parentId) {
            $roles = $this->roleRepository
                ->fetchAclRootRoles($ownershipContext, false);
        } else {
            $roles = $this->roleRepository
                ->fetchChildren($parentId, $ownershipContext);
        }

        $this->aclAccessExtensionService
            ->extendEntitiesWithAssignment($this->aclRepository, $baseRole, $roles);

        $this->aclAccessExtensionService
            ->extendEntitiesWithIdentityOwnership($this->aclRepository, $ownershipContext, $roles);

        return [
            'roles' => $roles,
            'baseRoleId' => $baseRole->id,
        ];
    }

    /**
     * @param  Request $request
     * @throws \Shopware\B2B\Common\Controller\B2bControllerForwardException
     */
    public function assignAction(Request $request)
    {
        $request->checkPost('index', ['roleId' => $request->getParam('baseRoleId')]);

        $roleId = (int) $request->requireParam('roleId');
        $baseRoleId = (int) $request->requireParam('baseRoleId');
        $grantable = (bool) $request->getParam('grantable', false);
        $ownershipContext = $this->authenticationService->getIdentity()->getOwnershipContext();

        $baseRole = $this->roleRepository->fetchOneById($baseRoleId, $ownershipContext);

        if ($request->getParam('allow', false)) {
            $this->aclRepository->allow($baseRole, $roleId, $grantable);
        } else {
            $this->aclRepository->deny($baseRole, $roleId);
        }

        throw new B2bControllerForwardException('index', null, null, ['roleId' => $baseRoleId]);
    }
}
