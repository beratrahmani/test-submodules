<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Frontend\DependencyInjection;

use Shopware\B2B\Address\Framework\DependencyInjection\AddressFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Order\Framework\DependencyInjection\OrderFrameworkConfiguration;
use Shopware\B2B\OrderList\Framework\DependencyInjection\OrderListFrameworkConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderFrontendConfiguration extends DependencyInjectionConfiguration
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
            new OrderListFrameworkConfiguration(),
            new AddressFrameworkConfiguration(),
            new OrderFrameworkConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
        ];
    }
}
