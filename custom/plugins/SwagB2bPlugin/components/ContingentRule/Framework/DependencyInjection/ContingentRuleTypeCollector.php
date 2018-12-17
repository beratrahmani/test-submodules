<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ContingentRuleTypeCollector implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $identityChain = $container->findDefinition('b2b_contingent_rule.entity_factory');
        $tags = $container->findTaggedServiceIds('b2b_contingent_rule.type');
        $serviceIds = array_keys($tags);

        foreach ($serviceIds as $serviceId) {
            $identityChain->addArgument(new Reference($serviceId));
        }
    }
}
