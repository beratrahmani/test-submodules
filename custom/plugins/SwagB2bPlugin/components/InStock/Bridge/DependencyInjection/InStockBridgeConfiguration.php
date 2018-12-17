<?php declare(strict_types=1);

namespace Shopware\B2B\InStock\Bridge\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Filter\DependencyInjection\FilterConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Bridge\DependencyInjection\StoreFrontAuthenticationBridgeConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InStockBridgeConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/bridge-services.xml',
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
            new StoreFrontAuthenticationBridgeConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
            new FilterConfiguration(),
        ];
    }
}
