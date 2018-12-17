<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Framework\DependencyInjection;

use Shopware\B2B\Cart\Bridge\DependencyInjection\CartBridgeConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\OrderClearance\Framework\DependencyInjection\OrderClearanceFrameworkConfiguration;
use Shopware\B2B\ProductName\Framework\DependencyInjection\ProductNameFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CartFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public static function createAclTables(): array
    {
        return [];
    }

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
        return [
            new CartAccessCollector(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new CartBridgeConfiguration(),
            new OrderClearanceFrameworkConfiguration(),
            new ProductNameFrameworkConfiguration(),
        ];
    }
}
