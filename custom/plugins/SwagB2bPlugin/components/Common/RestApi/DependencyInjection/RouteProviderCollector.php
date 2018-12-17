<?php declare(strict_types=1);

namespace Shopware\B2B\Common\RestApi\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RouteProviderCollector implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $identityChain = $container->findDefinition('b2b_common.routing_router');
        $tags = $container->findTaggedServiceIds('b2b_common.rest_route_provider');
        $serviceIds = array_keys($tags);

        $repositories = [];
        foreach ($serviceIds as $serviceId) {
            $repositories[] = new Reference($serviceId);
        }

        $identityChain->replaceArgument(0, $repositories);
    }
}
