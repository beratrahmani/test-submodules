<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework;

use Shopware\B2B\Common\B2BException;

class AclOperationNotPermittedException extends \RuntimeException implements B2BException
{
}
