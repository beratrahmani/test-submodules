<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Repository\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Filter\DependencyInjection\FilterConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RepositoryConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/repository-services.xml',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCompilerPasses(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new FilterConfiguration(),
        ];
    }
}
