<?php declare(strict_types=1);

namespace Shopware\B2B\RoleContingentGroup\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\DependencyInjection\AclFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\ContingentGroup\Framework\DependencyInjection\ContingentGroupFrameworkConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Shopware\B2B\RoleContingentGroup\Api\DependencyInjection\RoleContingentGroupApiConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoleContingentGroupFrameworkConfiguration extends DependencyInjectionConfiguration
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new AclFrameworkConfiguration(),
            new ContingentGroupFrameworkConfiguration(),
            new RoleFrameworkConfiguration(),
            new RoleContingentGroupApiConfiguration(),
        ];
    }
}
