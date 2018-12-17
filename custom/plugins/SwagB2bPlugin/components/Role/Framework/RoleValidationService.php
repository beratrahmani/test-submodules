<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleValidationService
{
    const CAUSE_DEPENDENCIES_EXIST = 'DependenciesExist';
    const CAUSE_ROOT_SIBLING = 'RootSibling';

    /**
     * @var ValidationBuilder
     */
    private $validationBuilder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**.
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param RoleRepository $roleRepository
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        RoleRepository $roleRepository
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param RoleEntity $role
     * @return Validator
     */
    public function createInsertValidation(RoleEntity $role): Validator
    {
        return $this->createCrudValidation($role)
            ->validateThat('id', $role->id)
            ->isBlank()

            ->getValidator($this->validator);
    }

    /**
     * @param RoleEntity $role
     * @return Validator
     */
    public function createUpdateValidation(RoleEntity $role): Validator
    {
        return $this->createCrudValidation($role)
            ->validateThat('id', $role->id)
            ->isNotBlank()

            ->getValidator($this->validator);
    }

    /**
     * @param RoleEntity $role
     * @param OwnershipContext $ownershipContext
     * @return Validator
     */
    public function createRemoveValidation(
        RoleEntity $role,
        OwnershipContext $ownershipContext
    ): Validator {
        return $this->validationBuilder
            ->validateThat('id', $role->id)
            ->isNotBlank()

            ->validateThat('Dependencies', $role->name)
            ->withCallback(
                function () use ($role, $ownershipContext): bool {
                    return $this->roleRepository->isRoleRemovable($ownershipContext, $role);
                },
                'The role can not be deleted due to dependencies.',
                self::CAUSE_DEPENDENCIES_EXIST
            )

            ->getValidator($this->validator);
    }

    /**
     * @param RoleEntity $role
     * @param RoleEntity $relatedRole
     * @param string $type
     * @return Validator
     */
    public function createMoveValidation(
        RoleEntity $role,
        RoleEntity $relatedRole,
        string $type
    ): Validator {
        return $this->validationBuilder
            ->validateThat('level', $role->level)
            ->isGreaterThan(0, true)

            ->validateThat('level', $relatedRole->level)
            ->withCallback(
                function (int $value) use ($type) {
                    return $value > 0 || $type === RoleCrudService::MOVE_AS_LAST_CHILD;
                },
                'The related role can not be the root node on this type',
                self::CAUSE_ROOT_SIBLING,
                [],
                true
            )

            ->validateThat('type', $type)
            ->isNotBlank()
            ->isInArray([
                RoleCrudService::MOVE_AS_PREV_SIBLING,
                RoleCrudService::MOVE_AS_NEXT_SIBLING,
                RoleCrudService::MOVE_AS_LAST_CHILD,
            ])

            ->getValidator($this->validator);
    }

    /**
     * @internal
     * @param RoleEntity $role
     * @return ValidationBuilder
     */
    protected function createCrudValidation(RoleEntity $role): ValidationBuilder
    {
        return $this->validationBuilder
            ->validateThat('name', $role->name)
            ->isNotBlank()
            ->isString()

            ->validateThat('contextOwnerId', $role->contextOwnerId)
            ->isNotBlank();
    }
}
