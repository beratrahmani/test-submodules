<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Shopware\B2B\Common\B2BException;

/**
 * Thrown if the context does not have any ACL mappings in relation to the current subject or context
 */
class AclUnsupportedContextException extends \InvalidArgumentException implements B2BException
{
}
