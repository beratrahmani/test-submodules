<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CartModeCollector implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->findDefinition('b2b_cart.mode_registry');
        $tags = $container->findTaggedServiceIds('b2b_cart.mode');
        $serviceIds = array_keys($tags);

        foreach ($serviceIds as $serviceId) {
            $registry->addArgument(new Reference($serviceId));
        }
    }
}
