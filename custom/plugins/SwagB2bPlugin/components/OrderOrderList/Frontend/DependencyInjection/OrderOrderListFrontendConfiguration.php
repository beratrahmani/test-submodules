<?php declare(strict_types=1);

namespace Shopware\B2B\OrderOrderList\Frontend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\OrderOrderList\Framework\DependencyInjection\OrderOrderListFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderOrderListFrontendConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/frontend-services.xml',
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
            new OrderOrderListFrameworkConfiguration(),
        ];
    }
}
