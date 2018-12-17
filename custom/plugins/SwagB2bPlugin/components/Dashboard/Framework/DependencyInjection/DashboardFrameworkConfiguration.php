<?php declare(strict_types=1);

namespace Shopware\B2B\Dashboard\Framework\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\ContingentGroup\Framework\DependencyInjection\ContingentGroupFrameworkConfiguration;
use Shopware\B2B\ContingentRule\Framework\DependencyInjection\ContingentRuleFrameworkConfiguration;
use Shopware\B2B\Dashboard\Bridge\DependencyInjection\DashboardBridgeConfiguration;
use Shopware\B2B\ProductName\Framework\DependencyInjection\ProductNameFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DashboardFrameworkConfiguration extends DependencyInjectionConfiguration
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
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new ContingentGroupFrameworkConfiguration(),
            new ContingentRuleFrameworkConfiguration(),
            new DashboardBridgeConfiguration(),
            new ProductNameFrameworkConfiguration(),
        ];
    }
}
