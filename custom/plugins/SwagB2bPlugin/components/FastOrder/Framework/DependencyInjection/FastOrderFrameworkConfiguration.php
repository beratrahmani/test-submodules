<?php declare(strict_types=1);

namespace Shopware\B2B\FastOrder\Framework\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\File\DependencyInjection\FileConfiguration;
use Shopware\B2B\FastOrder\Frontend\DependencyInjection\FastOrderFrontendConfiguration;
use Shopware\B2B\Order\Framework\DependencyInjection\OrderFrameworkConfiguration;
use Shopware\B2B\OrderList\Framework\DependencyInjection\OrderListFrameworkConfiguration;
use Shopware\B2B\OrderNumber\Framework\DependencyInjection\OrderNumberFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FastOrderFrameworkConfiguration extends DependencyInjectionConfiguration
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
            new OrderListFrameworkConfiguration(),
            new OrderFrameworkConfiguration(),
            new FastOrderFrontendConfiguration(),
            new FileConfiguration(),
            new OrderNumberFrameworkConfiguration(),
        ];
    }
}
