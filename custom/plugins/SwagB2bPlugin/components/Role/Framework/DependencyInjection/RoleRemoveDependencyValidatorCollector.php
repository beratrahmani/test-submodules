<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RoleRemoveDependencyValidatorCollector implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $repository = $container->findDefinition('b2b_role.repository');
        $tags = $container->findTaggedServiceIds('b2b_role.remove_dependency_validator');
        $serviceIds = array_keys($tags);

        $validators = [];
        foreach ($serviceIds as $serviceId) {
            $validators[] = new Reference($serviceId);
        }

        $repository->replaceArgument(5, $validators);
    }
}
