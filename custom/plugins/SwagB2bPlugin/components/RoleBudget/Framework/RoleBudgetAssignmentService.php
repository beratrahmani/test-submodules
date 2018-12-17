<?php declare(strict_types=1);

namespace Shopware\B2B\RoleBudget\Framework;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Role\Framework\RoleAssignmentValidationService;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class RoleBudgetAssignmentService extends AbstractCrudService
{
    const ENTITY_TYPE_NAME = 'budget';

    /**
     * @var RoleAssignmentValidationService
     */
    private $validationService;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var AclRepository
     */
    private $budgetAclRepository;

    /**
     * @param RoleAssignmentValidationService $validationService
     * @param RoleRepository $roleRepository
     * @param AclRepository $budgetAclRepository
     */
    public function __construct(
        RoleAssignmentValidationService $validationService,
        RoleRepository $roleRepository,
        AclRepository $budgetAclRepository
    ) {
        $this->validationService = $validationService;
        $this->roleRepository = $roleRepository;
        $this->budgetAclRepository = $budgetAclRepository;
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
                'budgetId',
                'roleId',
                'grantable',
                'allow',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     */
    public function allow(CrudServiceRequest $request, OwnershipContext $ownershipContext)
    {
        $roleId = (int) $request->requireParam('roleId');
        $budgetId = (int) $request->requireParam('budgetId');
        $grantable = false;
        if ($request->hasValueForParam('grantable')) {
            $grantable = (bool) $request->requireParam('grantable');
        }

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $validation = $this->validationService->createAllowValidation($role);
        $this->testValidation($role, $validation);

        $this->budgetAclRepository->allow($role, $budgetId, $grantable);
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     */
    public function deny(CrudServiceRequest $request, OwnershipContext $ownershipContext)
    {
        $roleId = (int) $request->requireParam('roleId');
        $budgetId = (int) $request->requireParam('budgetId');

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $validation = $this->validationService->createDenyValidation($role, $budgetId);
        $this->testValidation($role, $validation);

        $this->budgetAclRepository->deny($role, $budgetId);
    }
}
