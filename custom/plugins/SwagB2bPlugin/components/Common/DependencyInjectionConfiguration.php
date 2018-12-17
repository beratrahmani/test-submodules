<?php declare(strict_types=1);

namespace Shopware\B2B\Common;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class DependencyInjectionConfiguration
{
    /**
     * @param ContainerBuilder $containerBuilder
     * @return array
     */
    abstract public function getServiceFiles(ContainerBuilder $containerBuilder): array;

    /**
     * @return CompilerPassInterface[]
     */
    abstract public function getCompilerPasses(): array;

    /**
     * @return DependencyInjectionConfiguration[]
     */
    abstract public function getDependingConfigurations(): array;
}
