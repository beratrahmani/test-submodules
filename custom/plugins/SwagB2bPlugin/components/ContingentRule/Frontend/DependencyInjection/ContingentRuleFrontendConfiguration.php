<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Frontend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\ContingentRule\Framework\DependencyInjection\ContingentRuleFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContingentRuleFrontendConfiguration extends DependencyInjectionConfiguration
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
            new ContingentRuleFrameworkConfiguration(),
        ];
    }
}
