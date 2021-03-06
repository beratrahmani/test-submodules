<?php declare(strict_types=1);

namespace Shopware\B2B\OrderNumber\Framework\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\File\DependencyInjection\FileConfiguration;
use Shopware\B2B\OrderNumber\Bridge\DependencyInjection\OrderNumberBridgeConfiguration;
use Shopware\B2B\Shop\Framework\DependencyInjection\ShopFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderNumberFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/framework-services.xml',
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
            new OrderNumberBridgeConfiguration(),
            new ShopFrameworkConfiguration(),
            new FileConfiguration(),
        ];
    }
}
