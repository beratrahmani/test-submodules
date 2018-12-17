<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContact\Framework;

use Shopware\B2B\Acl\Framework\AclAccessWriter;
use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Acl\Framework\AclRepository;
use Shopware\B2B\Role\Framework\RoleEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class RoleContactAclAccessWriter extends AclAccessWriter
{
    /**
     * @var RoleContactRepository
     */
    private $roleContactRepository;

    /**
     * @param AclRepository $aclRepository
     * @param RoleContactRepository $roleContactRepository
     */
    public function __construct(AclRepository $aclRepository, RoleContactRepository $roleContactRepository)
    {
        parent::__construct($aclRepository);
        $this->roleContactRepository = $roleContactRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addNewSubject(
        OwnershipContext $context,
        AclGrantContext $grantContext,
        int $subjectId,
        bool $grantable = true
    ) {
        parent::addNewSubject($context, $grantContext, $subjectId, $grantable);

        if ($grantContext->getEntity() instanceof RoleEntity) {
            $this->roleContactRepository->assignRoleContact($grantContext->getEntity()->id, $subjectId);
        }
    }
}
