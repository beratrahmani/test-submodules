<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroupContact\Frontend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\ContingentGroupContact\Framework\DependencyInjection\ContingentGroupContactFrameworkConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Shopware\B2B\RoleContingentGroup\Framework\DependencyInjection\RoleContingentGroupFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContingentGroupContactFrontendConfiguration extends DependencyInjectionConfiguration
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
            new RoleContingentGroupFrameworkConfiguration(),
            new RoleFrameworkConfiguration(),
            new ContingentGroupContactFrameworkConfiguration(),
        ];
    }
}
