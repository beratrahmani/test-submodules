<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Repository;

use Shopware\B2B\Common\B2BException;

class CanNotUpdateExistingRecordException extends \DomainException implements B2BException
{
}
