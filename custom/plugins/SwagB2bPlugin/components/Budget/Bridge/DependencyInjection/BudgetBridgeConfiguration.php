<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Bridge\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Order\Framework\DependencyInjection\OrderFrameworkConfiguration;
use Shopware\B2B\OrderClearance\Framework\DependencyInjection\OrderClearanceFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BudgetBridgeConfiguration extends DependencyInjectionConfiguration
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
            new OrderClearanceFrameworkConfiguration(),
        ];
    }
}
