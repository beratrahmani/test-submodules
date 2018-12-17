<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Framework\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Shopware\B2B\Debtor\Framework\DependencyInjection\DebtorFrameworkConfiguration;
use Shopware\B2B\Price\Bridge\DependencyInjection\PriceBridgeConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PriceFrameworkConfiguration extends DependencyInjectionConfiguration
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
        return [];
    }

    /**
     * @return DependencyInjectionConfiguration[]
     */
    public function getDependingConfigurations(): array
    {
        return [
            new DebtorFrameworkConfiguration(),
            new RepositoryConfiguration(),
            new PriceBridgeConfiguration(),
        ];
    }
}
