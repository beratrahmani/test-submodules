<?php declare(strict_types=1);

namespace Shopware\B2B\Dashboard\Backend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Shopware\B2B\Dashboard\Bridge\DependencyInjection\DashboardBridgeConfiguration;
use Shopware\B2B\Debtor\Framework\DependencyInjection\DebtorFrameworkConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DashboardBackendConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @param ContainerBuilder $containerBuilder
     * @return string[]
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/backend-services.xml',
        ];
    }

    /**
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses(): array
    {
        return [];
    }

    /**
     * @return DependencyInjectionConfiguration[]
     */
    public function getDependingConfigurations(): array
    {
        return [
            new StoreFrontAuthenticationFrameworkConfiguration(),
            new ContactFrameworkConfiguration(),
            new DebtorFrameworkConfiguration(),
            new DashboardBridgeConfiguration(),
        ];
    }
}
