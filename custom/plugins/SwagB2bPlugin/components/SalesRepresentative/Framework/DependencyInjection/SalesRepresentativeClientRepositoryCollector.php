<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SalesRepresentativeClientRepositoryCollector implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $identityChain = $container->findDefinition('b2b_sales_representative.client_identity_loader');
        $tags = $container->findTaggedServiceIds('b2b_front_auth.authentication_repository');
        $serviceIds = array_keys($tags);

        $repositories = [];
        foreach ($serviceIds as $serviceId) {
            if (strpos($serviceId, 'sales_representative') !== false) {
                continue;
            }

            $repositories[] = new Reference($serviceId);
        }

        $identityChain->replaceArgument(0, $repositories);
    }
}
