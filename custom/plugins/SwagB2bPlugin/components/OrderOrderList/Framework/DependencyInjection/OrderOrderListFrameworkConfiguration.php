<?php declare(strict_types=1);

namespace Shopware\B2B\OrderOrderList\Framework\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\LineItemList\Framework\DependencyInjection\LineItemListFrameworkConfiguration;
use Shopware\B2B\OrderList\Framework\DependencyInjection\OrderListFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderOrderListFrameworkConfiguration extends DependencyInjectionConfiguration
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new LineItemListFrameworkConfiguration(),
            new OrderListFrameworkConfiguration(),
        ];
    }
}
