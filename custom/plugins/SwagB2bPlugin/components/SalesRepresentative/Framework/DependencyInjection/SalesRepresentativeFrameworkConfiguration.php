<?php declare(strict_types=1);

namespace Shopware\B2B\SalesRepresentative\Framework\DependencyInjection;

use Shopware\B2B\Address\Framework\DependencyInjection\AddressFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Debtor\Framework\DependencyInjection\DebtorFrameworkConfiguration;
use Shopware\B2B\SalesRepresentative\Bridge\DependencyInjection\SalesRepresentativeBridgeConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SalesRepresentativeFrameworkConfiguration extends DependencyInjectionConfiguration
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
        return [
            new SalesRepresentativeClientRepositoryCollector(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new AddressFrameworkConfiguration(),
            new DebtorFrameworkConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
            new SalesRepresentativeBridgeConfiguration(),
        ];
    }
}
