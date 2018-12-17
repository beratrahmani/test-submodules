<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Routing;

interface RouteProvider
{
    /**
     * @return array
     */
    public function getRoutes(): array;
}
