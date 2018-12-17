<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Controller;

use Shopware\B2B\Common\B2BException;

interface B2bControllerRoutingException extends B2BException
{
    /**
     * @return string
     */
    public function getAction(): string;

    /**
     * @return string|null
     */
    public function getController();

    /**
     * @return string|null
     */
    public function getModule();

    /**
     * @return array
     */
    public function getParams(): array;
}
