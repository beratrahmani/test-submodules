<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Framework;

/**
 * Assigns roles to contacts M:N
 */
class RoleContactService
{
    /**
     * @var RoleContactRepository
     */
    private $roleContactRepository;

    /**
     * @param RoleContactRepository $roleContactRepository
     */
    public function __construct(
        RoleContactRepository $roleContactRepository
    ) {
        $this->roleContactRepository = $roleContactRepository;
    }

    /**
     * @param int $contactId
     * @return array
     */
    public function getActiveRolesByContactId(int $contactId): array
    {
        return $this->roleContactRepository
            ->getActiveRoleIdsByContactId($contactId);
    }
}
