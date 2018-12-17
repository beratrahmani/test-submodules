<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Framework;

use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Shopware\B2B\Role\Framework\RoleAssignmentValidationService;
use Shopware\B2B\Role\Framework\RoleEntity;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleContactValidationService
{
    /**
     * @var ValidationBuilder
     */
    private $validationBuilder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var RoleContactRepository
     */
    private $roleContactRepository;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param RoleContactRepository $roleContactRepository
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        RoleContactRepository $roleContactRepository
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->roleContactRepository = $roleContactRepository;
    }

    /**
     * @param RoleEntity $roleEntity
     * @return \Shopware\B2B\Common\Validator\Validator
     */
    public function createAssignValidation(RoleEntity $roleEntity): Validator
    {
        return $this->validationBuilder
            ->validateThat('Level', $roleEntity->level)
            ->withCallback(
                function ($value): bool {
                    return $value > 0;
                },
                'This contact cannot be assigned to the company role.',
                RoleAssignmentValidationService::CAUSE_ROOT_ROLE
            )
            ->getValidator($this->validator);
    }

    /**
     * @param RoleEntity $roleEntity
     * @param int $contactId
     * @return \Shopware\B2B\Common\Validator\Validator
     */
    public function createRemoveAssignmentValidation(RoleEntity $roleEntity, int $contactId): Validator
    {
        return $this->validationBuilder
            ->validateThat('Dependencies', $contactId)
            ->withCallback(
                function ($contactId) use ($roleEntity) {
                    $ids = $this->roleContactRepository->getActiveRoleIdsByContactId($contactId);
                    $ids = array_map(function ($idRow): int {
                        return (int) $idRow['role_id'];
                    }, $ids);

                    return in_array($roleEntity->id, $ids, true) && count($ids) > 1;
                },
                'This contact must be at least assigned to one role.',
                RoleAssignmentValidationService::CAUSE_AT_LEAST_ONE_ROLE
            )
            ->getValidator($this->validator);
    }
}
