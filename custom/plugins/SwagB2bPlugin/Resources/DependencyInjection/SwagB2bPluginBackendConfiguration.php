<?php declare(strict_types=1);

namespace SwagB2bPlugin\Resources\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Dashboard\Backend\DependencyInjection\DashboardBackendConfiguration;
use Shopware\B2B\Offer\Backend\DependencyInjection\OfferBackendConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SwagB2bPluginBackendConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @param ContainerBuilder $containerBuilder
     * @return string[]
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/plugin-services.xml',
        ];
    }

    /**
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses(): array
    {
        return [];
    }

    /**
     * @return DependencyInjectionConfiguration[]
     */
    public function getDependingConfigurations(): array
    {
        return [
            new OfferBackendConfiguration(),
            new DashboardBackendConfiguration(),
        ];
    }
}
