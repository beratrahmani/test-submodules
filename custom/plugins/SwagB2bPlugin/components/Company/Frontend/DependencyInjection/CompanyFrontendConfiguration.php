<?php declare(strict_types=1);

namespace Shopware\B2B\Company\Frontend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Company\Framework\DependencyInjection\CompanyFrameworkConfiguration;
use Shopware\B2B\Role\Frontend\DependencyInjection\RoleFrontendConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompanyFrontendConfiguration extends DependencyInjectionConfiguration
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
            new CompanyFrameworkConfiguration(),
            new RoleFrontendConfiguration(),
        ];
    }
}
