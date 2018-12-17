<?php declare(strict_types=1);

namespace Shopware\B2B\Common\RestApi\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\MvcExtension\DependencyInjection\MvcExtensionConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RestApiConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/routing-services.xml',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCompilerPasses(): array
    {
        return [
            new RouteProviderCollector(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new MvcExtensionConfiguration(),
        ];
    }
}
