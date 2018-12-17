<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Bridge\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\LineItemList\Bridge\DependencyInjection\LineItemListBridgeConfiguration;
use Shopware\B2B\Order\Bridge\DependencyInjection\OrderBridgeConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderClearanceBridgeConfiguration extends DependencyInjectionConfiguration
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
            __DIR__ . '/bridge-services.xml',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCompilerPasses(): array
    {
        return [
            new OrderItemLoaderCollector(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new OrderBridgeConfiguration(),
            new LineItemListBridgeConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
        ];
    }
}
