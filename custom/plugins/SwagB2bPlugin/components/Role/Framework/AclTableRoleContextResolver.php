<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework;

use Shopware\B2B\Acl\Framework\AclContextResolverMain;
use Shopware\B2B\Acl\Framework\AclUnsupportedContextException;

class AclTableRoleContextResolver extends AclContextResolverMain
{
    /**
     * @param $context
     * @throws AclUnsupportedContextException
     * @return int
     */
    public function extractId($context): int
    {
        if ($context instanceof RoleEntity && !$context->isNew()) {
            return $context->id;
        }

        if ($context instanceof RoleAclGrantContext) {
            return $context->getEntity()->id;
        }

        throw new AclUnsupportedContextException(sprintf('unable to extract id from %s', get_class($context)));
    }
}
