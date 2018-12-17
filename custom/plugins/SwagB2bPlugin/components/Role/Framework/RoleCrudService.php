<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Acl\Framework\AclUnsupportedContextException;
use Shopware\B2B\Common\Service\AbstractCrudService;
use Shopware\B2B\Common\Service\CrudServiceRequest;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class RoleCrudService extends AbstractCrudService
{
    const MOVE_AS_PREV_SIBLING = 'prev-sibling';

    const MOVE_AS_NEXT_SIBLING = 'next-sibling';

    const MOVE_AS_LAST_CHILD = 'last-child';

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var RoleValidationService
     */
    private $validationService;

    /**
     * @var AclRepository
     */
    private $roleAclRepository;

    /**
     * @param RoleRepository $roleRepository
     * @param RoleValidationService $roleValidationService
     * @param AclRepository $roleAclRepository
     */
    public function __construct(
        RoleRepository $roleRepository,
        RoleValidationService $roleValidationService,
        AclRepository $roleAclRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->validationService = $roleValidationService;
        $this->roleAclRepository = $roleAclRepository;
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createNewRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'name',
                'contextOwnerId',
                'parentId',
            ]
        );
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createExistingRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'id',
                'name',
                'contextOwnerId',
            ]
        );
    }

    /**
     * @param array $data
     * @return CrudServiceRequest
     */
    public function createMoveRecordRequest(array $data): CrudServiceRequest
    {
        return new CrudServiceRequest(
            $data,
            [
                'type',
                'roleId',
                'relatedRoleId',
            ]
        );
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Validator\ValidationException
     * @return RoleEntity
     */
    public function create(CrudServiceRequest $request, OwnershipContext $ownershipContext): RoleEntity
    {
        $data = $request->getFilteredData();
        $parentId = (int) $request->requireParam('parentId');

        $role = new RoleEntity();

        $role->setData($data);
        $role->contextOwnerId = $ownershipContext->contextOwnerId;

        $validation = $this->validationService
            ->createInsertValidation($role);

        $this->testValidation($role, $validation);

        $this->roleRepository->fetchOneById($parentId, $ownershipContext);

        $role = $this->roleRepository
            ->addRole($role, $parentId);

        try {
            $this->roleAclRepository->allow(
                $ownershipContext,
                $role->id,
                true
            );
        } catch (AclUnsupportedContextException $e) {
            //nth
        }

        return $this->roleRepository->fetchOneById($role->id, $ownershipContext);
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @throws \Shopware\B2B\Common\Validator\ValidationException
     * @return RoleEntity
     */
    public function update(CrudServiceRequest $request, OwnershipContext $ownershipContext): RoleEntity
    {
        $data = $request->getFilteredData();

        $role = new RoleEntity();

        $role->setData($data);
        $role->contextOwnerId = $ownershipContext->contextOwnerId;

        $validation = $this->validationService
            ->createUpdateValidation($role);

        $this->testValidation($role, $validation);

        $role->id = (int) $role->id;

        return $this->roleRepository
            ->updateRole($role, $ownershipContext);
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @return RoleEntity
     */
    public function remove(CrudServiceRequest $request, OwnershipContext $ownershipContext): RoleEntity
    {
        $roleId = (int) $request->requireParam('id');
        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);

        $validation = $this->validationService->createRemoveValidation($role, $ownershipContext);

        $this->testValidation($role, $validation);

        $this->roleRepository->removeRole($role);

        return $role;
    }

    /**
     * @param CrudServiceRequest $request
     * @param OwnershipContext $ownershipContext
     * @return RoleEntity
     */
    public function move(CrudServiceRequest $request, OwnershipContext $ownershipContext): RoleEntity
    {
        $type = $request->requireParam('type');
        $roleId = (int) $request->requireParam('roleId');
        $relatedRoleId = (int) $request->requireParam('relatedRoleId');

        $role = $this->roleRepository->fetchOneById($roleId, $ownershipContext);
        $relatedRole = $this->roleRepository->fetchOneById($relatedRoleId, $ownershipContext);

        $validation = $this->validationService->createMoveValidation($role, $relatedRole, $type);

        $this->testValidation($role, $validation);

        switch ($type) {
            case self::MOVE_AS_PREV_SIBLING:
                $this->roleRepository
                    ->moveRoleAsPrevSibling($roleId, $relatedRoleId);

                break;
            case self::MOVE_AS_NEXT_SIBLING:
                $this->roleRepository
                    ->moveRoleAsNextSibling($roleId, $relatedRoleId);

                break;
            case self::MOVE_AS_LAST_CHILD:
                $this->roleRepository
                    ->moveRoleAsLastChild($roleId, $relatedRoleId);

                break;
        }

        return $this->roleRepository
            ->fetchOneById($roleId, $ownershipContext);
    }
}
