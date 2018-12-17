<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AuthenticationRepositoryCollector implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $identityChain = $container->findDefinition('b2b_front_auth.identity_chain_repository');
        $tags = $container->findTaggedServiceIds('b2b_front_auth.authentication_repository');
        $serviceIds = array_keys($tags);

        $repositories = [];
        foreach ($serviceIds as $serviceId) {
            $repositories[] = new Reference($serviceId);
        }

        $identityChain->replaceArgument(0, $repositories);
    }
}
