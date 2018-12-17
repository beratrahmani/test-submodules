<?php declare(strict_types=1);

namespace Shopware\B2B\Common\RestApi;

use Shopware\B2B\Common\Routing\RouteProvider;

class RootRouteProvider implements RouteProvider
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        return [
            ['GET', '', 'b2b_common.routing_root_controller', 'index'],
        ];
    }
}
