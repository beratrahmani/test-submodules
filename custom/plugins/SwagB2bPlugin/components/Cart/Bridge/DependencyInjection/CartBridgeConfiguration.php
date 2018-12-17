<?php declare(strict_types=1);

namespace Shopware\B2B\Cart\Bridge\DependencyInjection;

use Shopware\B2B\AclRoute\Framework\DependencyInjection\AclRouteFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Debtor\Framework\DependencyInjection\DebtorFrameworkConfiguration;
use Shopware\B2B\Order\Framework\DependencyInjection\OrderFrameworkConfiguration;
use Shopware\B2B\OrderClearance\Framework\DependencyInjection\OrderClearanceFrameworkConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CartBridgeConfiguration extends DependencyInjectionConfiguration
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
        return [
            new CartModeCollector(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new StoreFrontAuthenticationFrameworkConfiguration(),
            new OrderFrameworkConfiguration(),
            new OrderClearanceFrameworkConfiguration(),
            new DebtorFrameworkConfiguration(),
            new AclRouteFrameworkConfiguration(),
        ];
    }
}
