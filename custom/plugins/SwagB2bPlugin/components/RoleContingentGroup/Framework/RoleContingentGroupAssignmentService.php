<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContingentGroup\Framework;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupRepository;
use Shopware\B2B\Role\Framework\RoleAssignmentValidationService;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

/**
 * Assigns roles to contingent groups M:N
 */
class RoleContingentGroupAssignmentService extends AbstractCrudService
{
    const ENTITY_TYPE_NAME = 'contingent';

    /**
     * @var RoleContingentGroupRepository
     */
    private $roleContingentGroupRepository;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var ContingentGroupRepository
     */
    private $contingentGroupRepository;

    /**
     * @var AclRepository
     */
    private $roleContingentGroupAclRepository;

    /**
     * @var RoleAssignmentValidationService
     */
    private $validationService;

    /**
     * @param RoleContingentGroupRepository $roleContingentGroupRepository
     * @param RoleRepository $roleRepository
     * @param ContingentGroupRepository $contingentGroupRepository
     * @param RoleAssignmentValidationService $validationService
     * @param AclRepository $roleContingentAclRepository
     */
    public function __construct(
        RoleContingentGroupRepository $roleContingentGroupRepository,
        RoleRepository $roleRepository,
        ContingentGroupRepository $contingentGroupRepository,
        RoleAssignmentValidationService $validationService,
        AclRepository $roleContingentAclRepository
    ) {
        $this->roleContingentGroupRepository = $roleContingentGroupRepository;
        $this->roleRepository = $roleRepository;
        $this->contingentGroupRepository = $contingentGroupRepository;
        $this->validationService = $validationService;
        $this->roleContingentGroupAclRepository = $roleContingentAclRepository;
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createAssignRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'contingentGroupId',
                'roleId',
                'grantable',
                'allow',
                'assignmentId',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     */
    public function assign(CrudServiceRequest $request, OwnershipContext $ownershipContext)
    {
        $roleId = (int) $request->requireParam('roleId');
        $contingentGroupId = (int) $request->requireParam('contingentGroupId');

        $this->checkAccess($roleId, $contingentGroupId, $ownershipContext);

        $grantable = false;
        if ($request->hasValueForParam('grantable')) {
            $grantable = (bool) $request->requireParam('grantable');
        }

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $validation = $this->validationService->createAllowValidation($role);
        $this->testValidation($role, $validation);

        $this->roleContingentGroupAclRepository->allow($role, $contingentGroupId, $grantable);

        if (!$request->hasValueForParam('assignmentId')) {
            $this->roleContingentGroupRepository
                ->assignRoleContingentGroup($roleId, $contingentGroupId);
        }
    }

    /**
     * @param int $roleId
     * @param int $contingentGroupId
     * @param OwnershipContext $ownershipContext
     */
    protected function checkAccess(int $roleId, int $contingentGroupId, OwnershipContext $ownershipContext)
    {
        $this->roleRepository->fetchOneById($roleId, $ownershipContext);
        $this->contingentGroupRepository->fetchOneById($contingentGroupId, $ownershipContext);
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     */
    public function removeAssignment(CrudServiceRequest $request, OwnershipContext $ownershipContext)
    {
        $roleId = (int) $request->requireParam('roleId');
        $contingentGroupId = (int) $request->requireParam('contingentGroupId');

        $this->checkAccess($roleId, $contingentGroupId, $ownershipContext);

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $validation = $this->validationService->createDenyValidation($role, $contingentGroupId);
        $this->testValidation($role, $validation);

        $this->roleContingentGroupRepository
            ->removeRoleContingentGroupAssignment($roleId, $contingentGroupId);

        $this->roleContingentGroupAclRepository->deny($role, $contingentGroupId);
    }
}
