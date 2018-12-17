<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Controller;

/**
 * Use in case of unhandled success response
 */
class EmptyForwardException extends B2bControllerForwardException
{
    /**
     * @param int $code
     * @param null|\Exception $previous
     */
    public function __construct($code = 0, \Exception $previous = null)
    {
        parent::__construct('index', 'b2bempty', 'frontend', [], $code, $previous);
    }
}
