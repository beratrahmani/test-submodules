<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Common\Validator\ValidationBuilder;
use Shopware\B2B\Common\Validator\Validator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleAssignmentValidationService
{
    const CAUSE_AT_LEAST_ONE_ROLE = 'AtLeastOneRole';
    const CAUSE_ROOT_ROLE = 'RootRole';

    /**
     * @var ValidationBuilder
     */
    private $validationBuilder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @param ValidationBuilder $validationBuilder
     * @param ValidatorInterface $validator
     * @param AclRepository $aclRepository
     * @param string $entityType
     */
    public function __construct(
        ValidationBuilder $validationBuilder,
        ValidatorInterface $validator,
        AclRepository $aclRepository,
        String $entityType
    ) {
        $this->validationBuilder = $validationBuilder;
        $this->validator = $validator;
        $this->aclRepository = $aclRepository;
        $this->entityType = $entityType;
    }

    /**
     * @param RoleEntity $roleEntity
     * @return \Shopware\B2B\Common\Validator\Validator
     */
    public function createAllowValidation(RoleEntity $roleEntity): Validator
    {
        return $this->validationBuilder
            ->validateThat('Level', $roleEntity->level)
            ->withCallback(
                function ($value): bool {
                    return $value > 0;
                },
                'This ' . $this->entityType . ' cannot be assigned to the company role.',
                self::CAUSE_ROOT_ROLE
            )
            ->getValidator($this->validator);
    }

    /**
     * @param RoleEntity $roleEntity
     * @param int $subjectId
     * @return \Shopware\B2B\Common\Validator\Validator
     */
    public function createDenyValidation(RoleEntity $roleEntity, int $subjectId): Validator
    {
        return $this->validationBuilder
            ->validateThat('Dependencies', $subjectId)
            ->withCallback(
                function ($subjectId) use ($roleEntity) {
                    $ids = $this->aclRepository->getAllAssignedIdsBySubjectId($roleEntity, $subjectId);

                    return in_array($roleEntity->id, $ids, true) && count($ids) > 1;
                },
                'This ' . $this->entityType . ' must be at least assigned to one role.',
                self::CAUSE_AT_LEAST_ONE_ROLE
            )
            ->getValidator($this->validator);
    }
}
