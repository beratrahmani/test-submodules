<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Controller;

interface ControllerProxyInterface
{
    public function dispatch($action);

    /**
     * @param string $name
     * @param null $value
     * @return mixed|void
     */
    public function __call($name, $value = null);
}
