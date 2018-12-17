<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework;

use Shopware\B2B\Common\B2BException;

class NotAuthenticatedException extends \RuntimeException implements B2BException
{
}
