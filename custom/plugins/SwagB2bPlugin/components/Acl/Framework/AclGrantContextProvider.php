<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Shopware\B2B\StoreFrontAuthentication\Framework\OwnershipContext;

interface AclGrantContextProvider
{
    /**
     * @param string $identifier
     * @param OwnershipContext $ownershipContext
     * @return AclGrantContext
     */
    public function fetchOneByIdentifier(string $identifier, OwnershipContext $ownershipContext): AclGrantContext;
}
