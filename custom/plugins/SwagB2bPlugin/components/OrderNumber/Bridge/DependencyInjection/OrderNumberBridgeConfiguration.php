<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Bridge\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderNumberBridgeConfiguration extends DependencyInjectionConfiguration
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
            new RepositoryConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
        ];
    }
}
