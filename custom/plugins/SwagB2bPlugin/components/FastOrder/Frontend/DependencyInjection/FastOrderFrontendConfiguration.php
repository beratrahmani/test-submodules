<?php declare(strict_types=1);

namespace Shopware\B2B\FastOrder\Frontend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\FastOrder\Framework\DependencyInjection\FastOrderFrameworkConfiguration;
use Shopware\B2B\OrderList\Frontend\DependencyInjection\OrderListFrontendConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FastOrderFrontendConfiguration extends DependencyInjectionConfiguration
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
            new FastOrderFrameworkConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
            new OrderListFrontendConfiguration(),
        ];
    }
}
