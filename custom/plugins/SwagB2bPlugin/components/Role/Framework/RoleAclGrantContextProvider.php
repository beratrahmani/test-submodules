<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Acl\Framework\AclGrantContextProvider;
use Shopware\B2B\Acl\Framework\AclUnsupportedIdentifierException;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class RoleAclGrantContextProvider implements AclGrantContextProvider
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
     * {@inheritdoc}
     * @throws AclUnsupportedIdentifierException
     */
    public function fetchOneByIdentifier(string $identifier, OwnershipContext $ownershipContext): AclGrantContext
    {
        if (strpos($identifier, '::') === false) {
            throw new AclUnsupportedIdentifierException(sprintf($identifier . ' not supported'));
        }

        list($className, $id) = explode('::', $identifier, 2);

        if ($className !== RoleEntity::class) {
            throw new AclUnsupportedIdentifierException($identifier . ' not supported');
        }

        $role = $this->roleRepository
            ->fetchOneById((int) $id, $ownershipContext);

        return new RoleAclGrantContext($role);
    }
}
