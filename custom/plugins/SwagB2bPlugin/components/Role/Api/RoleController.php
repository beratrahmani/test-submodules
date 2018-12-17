<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Api;

use Shopware\B2B\Common\Controller\GridHelper;
use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\Role\Framework\RoleCrudService;
use Shopware\B2B\Role\Framework\RoleEntity;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\Role\Framework\RoleSearchStruct;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

class RoleController
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var GridHelper
     */
    private $requestHelper;

    /**
     * @var RoleCrudService
     */
    private $roleCrudService;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $contextIdentityLoader;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @param RoleRepository $roleRepository
     * @param GridHelper $requestHelper
     * @param RoleCrudService $roleCrudService
     * @param DebtorAuthenticationIdentityLoader $contextIdentityLoader
     * @param LoginContextService $loginContextService
     */
    public function __construct(
        RoleRepository $roleRepository,
        GridHelper $requestHelper,
        RoleCrudService $roleCrudService,
        DebtorAuthenticationIdentityLoader $contextIdentityLoader,
        LoginContextService $loginContextService
    ) {
        $this->roleRepository = $roleRepository;
        $this->requestHelper = $requestHelper;
        $this->roleCrudService = $roleCrudService;
        $this->contextIdentityLoader = $contextIdentityLoader;
        $this->loginContextService = $loginContextService;
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function getListAction(string $debtorEmail, Request $request): array
    {
        $search = new RoleSearchStruct();

        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $this->requestHelper
            ->extractSearchDataInRestApi($request, $search);

        $roles = $this->roleRepository
            ->fetchList($search, $ownershipContext);

        $totalCount = $this->roleRepository
            ->fetchTotalCount($search, $ownershipContext);

        $this->removeEmptyChildren(...$roles);

        return ['success' => true, 'roles' => $roles, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @return array
     */
    public function getAction(string $debtorEmail, int $roleId): array
    {
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $role = $this->roleRepository
            ->fetchOneById($roleId, $ownershipContext);

        $this->removeEmptyChildren($role);

        return ['success' => true, 'role' => $role];
    }

    /**
     * @param string $debtorEmail
     * @param int $parentId
     * @return array
     */
    public function getChildrenAction(string $debtorEmail, int $parentId): array
    {
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $children = $this->roleRepository->fetchChildren($parentId, $ownershipContext);

        $this->removeEmptyChildren(...$children);

        return ['success' => true, 'children' => $children];
    }

    /**
     * @param string $debtorEmail
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, Request $request): array
    {
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $newRecord = $this->roleCrudService
            ->createNewRecordRequest($request->getPost());

        $role = $this->roleCrudService
            ->create($newRecord, $ownershipContext);

        $this->removeEmptyChildren($role);

        return ['success' => true, 'role' => $role];
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @param Request $request
     * @return array
     */
    public function updateAction(string $debtorEmail, int $roleId, Request $request): array
    {
        $data = array_merge(
            $request->getPost(),
            ['id' => $roleId]
        );

        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $existingRecord = $this->roleCrudService
            ->createExistingRecordRequest($data);

        $role = $this->roleCrudService
            ->update($existingRecord, $ownershipContext);

        $this->removeEmptyChildren($role);

        return ['success' => true, 'role' => $role];
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @param Request $request
     * @return array
     */
    public function moveAction(string $debtorEmail, int $roleId, Request $request)
    {
        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $data = array_merge(
            $request->getPost(),
            ['roleId' => $roleId]
        );

        $moveRecord = $this->roleCrudService->createMoveRecordRequest($data);

        $role = $this->roleCrudService->move($moveRecord, $ownershipContext);

        $this->removeEmptyChildren($role);

        return ['success' => true, 'role' => $role];
    }

    /**
     * @param string $debtorEmail
     * @param int $roleId
     * @return array
     */
    public function removeAction(string $debtorEmail, int $roleId): array
    {
        $data = [
            'id' => $roleId,
        ];

        $ownershipContext = $this->contextIdentityLoader
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();
        $existingRecord = $this->roleCrudService
            ->createExistingRecordRequest($data);

        $role = $this->roleCrudService
            ->remove($existingRecord, $ownershipContext);

        $this->removeEmptyChildren($role);

        return ['success' => true, 'role' => $role];
    }

    /**
     * @param RoleEntity ...$roles
     */
    protected function removeEmptyChildren(RoleEntity ...$roles)
    {
        foreach ($roles as $role) {
            if (empty($role->children)) {
                unset($role->children);
            }
        }
    }
}
