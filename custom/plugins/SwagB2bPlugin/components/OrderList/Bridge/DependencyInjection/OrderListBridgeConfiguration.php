<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Bridge\DependencyInjection;

use Shopware\B2B\Cart\Bridge\DependencyInjection\CartBridgeConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\LineItemList\Framework\DependencyInjection\LineItemListFrameworkConfiguration;
use Shopware\B2B\Order\Framework\DependencyInjection\OrderFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderListBridgeConfiguration extends DependencyInjectionConfiguration
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
            new OrderFrameworkConfiguration(),
            new CartBridgeConfiguration(),
            new LineItemListFrameworkConfiguration(),
        ];
    }
}
