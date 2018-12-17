<?php declare(strict_types=1);

namespace Shopware\B2B\Statistic\Frontend\DependencyInjection;

use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Statistic\Framework\DependencyInjection\StatisticFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StatisticFrontendConfiguration extends DependencyInjectionConfiguration
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
            new StatisticFrameworkConfiguration(),
            new ControllerConfiguration(),
        ];
    }
}
