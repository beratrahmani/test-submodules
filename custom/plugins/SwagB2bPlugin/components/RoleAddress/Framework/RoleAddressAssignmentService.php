<?php declare(strict_types=1);

namespace Shopware\B2B\RoleAddress\Framework;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\Role\Framework\RoleAssignmentValidationService;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class RoleAddressAssignmentService extends AbstractCrudService
{
    const ENTITY_TYPE_NAME = 'address';

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
    private $addressAclRepository;

    /**
     * @param RoleAssignmentValidationService $validationService
     * @param RoleRepository $roleRepository
     * @param AclRepository $addressAclRepository
     */
    public function __construct(
        RoleAssignmentValidationService $validationService,
        RoleRepository $roleRepository,
        AclRepository $addressAclRepository
    ) {
        $this->validationService = $validationService;
        $this->roleRepository = $roleRepository;
        $this->addressAclRepository = $addressAclRepository;
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
                'addressId',
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
        $addressId = (int) $request->requireParam('addressId');
        $grantable = false;
        if ($request->hasValueForParam('grantable')) {
            $grantable = (bool) $request->requireParam('grantable');
        }

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $validation = $this->validationService->createAllowValidation($role);
        $this->testValidation($role, $validation);

        $this->addressAclRepository->allow($role, $addressId, $grantable);
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     */
    public function deny(CrudServiceRequest $request, OwnershipContext $ownershipContext)
    {
        $roleId = (int) $request->requireParam('roleId');
        $addressId = (int) $request->requireParam('addressId');

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $validation = $this->validationService->createDenyValidation($role, $addressId);
        $this->testValidation($role, $validation);

        $this->addressAclRepository->deny($role, $addressId);
    }
}
