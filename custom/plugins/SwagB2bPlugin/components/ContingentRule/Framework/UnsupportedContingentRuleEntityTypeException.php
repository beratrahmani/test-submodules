<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework;

use Exception;
use Shopware\B2B\Common\B2BException;

class UnsupportedContingentRuleEntityTypeException extends \DomainException implements B2BException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($entity, $code = 0, Exception $previous = null)
    {
        $message = 'Can not handle ' . get_class($entity) . '.';
        parent::__construct($message, $code, $previous);
    }
}
