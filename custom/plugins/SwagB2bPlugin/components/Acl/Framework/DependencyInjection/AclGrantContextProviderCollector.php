<?php declare(strict_types=1);

namespace Shopware\B2B\Acl\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AclGrantContextProviderCollector implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $providerChain = $container->findDefinition('b2b_acl.grant_context_provider_chain');
        $tags = $container->findTaggedServiceIds('b2b_acl.grant_context_provider');
        $serviceIds = array_keys($tags);

        foreach ($serviceIds as $serviceId) {
            $providerChain->addArgument(new Reference($serviceId));
        }
    }
}
