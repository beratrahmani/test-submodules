<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContingentGroup\Framework;

use Shopware\B2B\Acl\Framework\AclAccessWriterInterface;
use Shopware\B2B\Acl\Framework\AclGrantContext;
use Shopware\B2B\Role\Framework\RoleEntity;
use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class RoleContingentGroupAclAccessWriter implements AclAccessWriterInterface
{
    /**
     * @var RoleContingentGroupRepository
     */
    private $roleContingentGroupRepository;

    /**
     * @var AclAccessWriterInterface
     */
    private $decoratedService;

    /**
     * @param AclAccessWriterInterface $decoratedService
     * @param RoleContingentGroupRepository $roleContingentGroupRepository
     */
    public function __construct(
        AclAccessWriterInterface $decoratedService,
        RoleContingentGroupRepository $roleContingentGroupRepository
    ) {
        $this->decoratedService = $decoratedService;
        $this->roleContingentGroupRepository = $roleContingentGroupRepository;
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
        $this->decoratedService->addNewSubject($context, $grantContext, $subjectId, $grantable);

        if ($grantContext->getEntity() instanceof RoleEntity) {
            $this->roleContingentGroupRepository->assignRoleContingentGroup($grantContext->getEntity()->id, $subjectId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function testUpdateAllowed(OwnershipContext $ownershipContext, int $subjectId)
    {
        $this->decoratedService->testUpdateAllowed($ownershipContext, $subjectId);
    }
}
