<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Framework;

use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Validator\ValidationException;
use Shopware\B2B\Role\Framework\RoleRepository;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

/**
 * Assigns roles to contacts M:N
 */
class RoleContactAssignmentService extends AbstractCrudService
{
    /**
     * @var RoleContactRepository
     */
    private $roleContactRepository;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var RoleContactValidationService
     */
    private $validationService;

    /**
     * @param RoleContactRepository $roleContactRepository
     * @param RoleRepository $roleRepository
     * @param RoleContactValidationService $validationService
     */
    public function __construct(
        RoleContactRepository $roleContactRepository,
        RoleRepository $roleRepository,
        RoleContactValidationService $validationService
    ) {
        $this->roleContactRepository = $roleContactRepository;
        $this->roleRepository = $roleRepository;
        $this->validationService = $validationService;
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param int $roleId
     * @param int $contactId
     * @throws MismatchingDataException
     * @throws ValidationException
     */
    public function assign(OwnershipContext $ownershipContext, int $roleId, int $contactId)
    {
        if (!$this->roleContactRepository->isRoleDebtorContactDebtor($ownershipContext, $roleId, $contactId)) {
            throw new MismatchingDataException(sprintf('Can not assign contact "%s" to role "%s"', $contactId, $roleId));
        }

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $validator = $this->validationService->createAssignValidation($role);
        $this->testValidation($role, $validator);

        $this->roleContactRepository
            ->assignRoleContact($roleId, $contactId);
    }

    /**
     * @param OwnershipContext $ownershipContext
     * @param int $roleId
     * @param int $contactId
     * @throws ValidationException
     */
    public function removeAssignment(OwnershipContext $ownershipContext, int $roleId, int $contactId)
    {
        if (!$this->roleContactRepository->isRoleDebtorContactDebtor($ownershipContext, $roleId, $contactId)) {
            throw new MismatchingDataException(sprintf('Can not assign contact "%s" to role "%s"', $contactId, $roleId));
        }

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $validator = $this->validationService->createRemoveAssignmentValidation($role, $contactId);
        $this->testValidation($role, $validator);

        $this->roleContactRepository
            ->removeRoleContactAssignment($roleId, $contactId);
    }
}
