<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CartAccessCollector implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $identityChain = $container->findDefinition('b2b_cart.cart_access');
        $tags = $container->findTaggedServiceIds('b2b_cart.cart_access_strategies');
        $serviceIds = array_keys($tags);

        foreach ($serviceIds as $serviceId) {
            $identityChain->addArgument(new Reference($serviceId));
        }
    }
}
