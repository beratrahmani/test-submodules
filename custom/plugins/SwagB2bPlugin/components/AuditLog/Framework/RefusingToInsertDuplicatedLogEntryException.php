<?php declare(strict_types = 1);

namespace Shopware\B2B\AuditLog\Framework;

use Shopware\B2B\Common\Repository\CanNotInsertExistingRecordException;

class RefusingToInsertDuplicatedLogEntryException extends CanNotInsertExistingRecordException
{
}
