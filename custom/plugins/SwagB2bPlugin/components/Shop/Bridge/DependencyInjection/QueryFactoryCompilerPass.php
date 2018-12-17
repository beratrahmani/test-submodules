<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Bridge\DependencyInjection;

use Shopware\B2B\Shop\Bridge\QueryBuilderFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class QueryFactoryCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('shopware_searchdbal.dbal_query_builder_factory');
        $args = $definition->getArguments();

        $newDefinition = new Definition(QueryBuilderFactory::class, $args);

        $container->setDefinition('shopware_searchdbal.dbal_query_builder_factory', $newDefinition);
    }
}
