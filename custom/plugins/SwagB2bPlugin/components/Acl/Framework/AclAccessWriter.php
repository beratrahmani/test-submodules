<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

class AclAccessWriter implements AclAccessWriterInterface
{
    /**
     * @var AclRepository
     */
    private $aclRepository;

    /**
     * @param AclRepository $aclRepository
     */
    public function __construct(AclRepository $aclRepository)
    {
        $this->aclRepository = $aclRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addNewSubject(OwnershipContext $context, AclGrantContext $grantContext, int $subjectId, bool $grantable = true)
    {
        try {
            $this->aclRepository->allow($context, $subjectId, true);
        } catch (AclUnsupportedContextException $e) {
            //nth
        }

        $this->aclRepository->allow($grantContext->getEntity(), $subjectId, $grantable);
    }

    /**
     * {@inheritdoc}
     * @throws AclOperationNotPermittedException
     */
    public function testUpdateAllowed(OwnershipContext $ownershipContext, int $subjectId)
    {
        try {
            if ($this->aclRepository->isAllowed($ownershipContext, $subjectId)) {
                return;
            }
        } catch (AclUnsupportedContextException $e) {
            return;
        }

        throw new AclOperationNotPermittedException('Update not allowed');
    }
}
