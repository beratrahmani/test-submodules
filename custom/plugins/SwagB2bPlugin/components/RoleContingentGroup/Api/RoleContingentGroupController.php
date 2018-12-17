<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContingentGroup\Api;

use Shopware\B2B\Common\MvcExtension\Request;
use Shopware\B2B\Debtor\Framework\DebtorAuthenticationIdentityLoader;
use Shopware\B2B\RoleContingentGroup\Framework\RoleContingentGroupAssignmentService;
use Shopware\B2B\RoleContingentGroup\Framework\RoleContingentGroupRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\LoginContextService;

class RoleContingentGroupController
{
    /**
     * @var RoleContingentGroupAssignmentService
     */
    private $roleContingentGroupAssignmentService;

    /**
     * @var DebtorAuthenticationIdentityLoader
     */
    private $debtorRepository;

    /**
     * @var LoginContextService
     */
    private $loginContextService;

    /**
     * @var RoleContingentGroupRepository
     */
    private $roleContingentGroupRepository;

    /**
     * @param RoleContingentGroupRepository $roleContingentGroupRepository
     * @param RoleContingentGroupAssignmentService $roleContingentGroupAssignmentService
     * @param DebtorAuthenticationIdentityLoader $debtorRepository
     * @param LoginContextService $loginContextService
     */
    public function __construct(
        RoleContingentGroupRepository $roleContingentGroupRepository,
        RoleContingentGroupAssignmentService $roleContingentGroupAssignmentService,
        DebtorAuthenticationIdentityLoader $debtorRepository,
        LoginContextService $loginContextService
    ) {
        $this->roleContingentGroupAssignmentService = $roleContingentGroupAssignmentService;
        $this->debtorRepository = $debtorRepository;
        $this->loginContextService = $loginContextService;
        $this->roleContingentGroupRepository = $roleContingentGroupRepository;
    }

    /**
     * @param string $debtorEmail
     * @param string $roleId
     * @return array
     */
    public function getListAction(string $debtorEmail, string $roleId): array
    {
        $context = $this->debtorRepository
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $roles = $this->roleContingentGroupRepository
            ->fetchAllRolesAndCheckForContingentGroupAssignment((int) $roleId, $context);

        $totalCount = count($roles);

        return ['success' => true, 'roles' => $roles, 'totalCount' => $totalCount];
    }

    /**
     * @param string $debtorEmail
     * @param string $roleId
     * @param Request $request
     * @return array
     */
    public function createAction(string $debtorEmail, string $roleId, Request $request): array
    {
        $ownershipContext = $this->debtorRepository
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $crudRequest = $this->roleContingentGroupAssignmentService->createAssignRecordRequest(
            array_merge(
                $request->getPost(),
                ['roleId' => (int) $roleId]
            )
        );

        $this->roleContingentGroupAssignmentService
            ->assign($crudRequest, $ownershipContext);

        return ['success' => true];
    }

    /**
     * @param string $debtorEmail
     * @param string $roleId
     * @param Request $request
     * @return array
     */
    public function removeAction(string $debtorEmail, string $roleId, Request $request): array
    {
        $ownershipContext = $this->debtorRepository
            ->fetchIdentityByEmail($debtorEmail, $this->loginContextService)
            ->getOwnershipContext();

        $crudRequest = $this->roleContingentGroupAssignmentService->createAssignRecordRequest(
            array_merge(
                $request->getPost(),
                ['roleId' => (int) $roleId]
            )
        );

        $this->roleContingentGroupAssignmentService
            ->removeAssignment($crudRequest, $ownershipContext);

        return ['success' => true];
    }
}
