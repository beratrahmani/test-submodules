<?php declare(strict_types=1);

namespace Shopware\B2B\Shop\Framework\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Shop\Bridge\DependencyInjection\ShopBridgeConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ShopFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [];
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
            new ShopBridgeConfiguration(),
        ];
    }
}
