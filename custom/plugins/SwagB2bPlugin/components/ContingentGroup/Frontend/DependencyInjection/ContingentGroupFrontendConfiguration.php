<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Frontend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Company\Frontend\DependencyInjection\CompanyFrontendConfiguration;
use Shopware\B2B\ContingentGroup\Framework\DependencyInjection\ContingentGroupFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContingentGroupFrontendConfiguration extends DependencyInjectionConfiguration
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
            new CompanyFrontendConfiguration(),
        ];
    }
}
