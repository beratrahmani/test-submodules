<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface AclAccessWriterInterface
{
    /**
     * @param OwnershipContext $context
     * @param AclGrantContext $grantContext
     * @param int $subjectId
     * @param bool $grantable
     */
    public function addNewSubject(OwnershipContext $context, AclGrantContext $grantContext, int $subjectId, bool $grantable = true);

    /**
     * @param OwnershipContext $ownershipContext
     * @param int $subjectId
     */
    public function testUpdateAllowed(OwnershipContext $ownershipContext, int $subjectId);
}
