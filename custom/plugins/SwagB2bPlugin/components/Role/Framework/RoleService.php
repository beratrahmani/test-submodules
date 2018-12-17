<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class RoleService
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @param RoleRepository $roleRepository
     */
    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param int[] $openNodeIds
     * @param OwnershipContext $ownershipContext
     * @return array
     */
    public function createSubtree(array $openNodeIds, OwnershipContext $ownershipContext): array
    {
        $rootRoles = $this->roleRepository->fetchAclRootRoles($ownershipContext, true);

        $allowedRoleRootIds = array_map(function (RoleEntity $role) {
            return $role->id;
        }, $rootRoles);

        $roles = $this->roleRepository
            ->fetchSubtree(
                array_merge(
                    $openNodeIds,
                    $allowedRoleRootIds
                ),
                $ownershipContext
            );

        foreach ($roles as $nodeRole) {
            $nodeRole->isForbidden = !$this->isAllowed($rootRoles, $nodeRole);
            $nodeRole->isAllowedRoot = in_array($nodeRole->id, $allowedRoleRootIds, true);
        }

        return $roles;
    }

    /**
     * @param RoleEntity[] $allowedRoles
     * @param RoleEntity $nodeRole
     * @return bool
     */
    protected function isAllowed(array $allowedRoles, RoleEntity $nodeRole): bool
    {
        foreach ($allowedRoles as $allowedRole) {
            if ($allowedRole->left <= $nodeRole->left && $allowedRole->right >= $nodeRole->right) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $tree
     * @param RoleEntity $role
     * @throws \DomainException
     * @return array
     */
    public function mapToTree(array $tree, RoleEntity $role): array
    {
        /** @var RoleEntity $element */
        foreach ($tree as $element) {
            if ($element->left <= $role->left && $element->right >= $role->right) {
                if ($element->level+1 === $role->level) {
                    $element->children[] = $role;
                } else {
                    $this->mapToTree($element->children, $role);
                }

                return $tree;
            }
        }

        throw new \DomainException(sprintf('Unable to resolve node position of %s with id %s', $role->name, $role->id));
    }
}
