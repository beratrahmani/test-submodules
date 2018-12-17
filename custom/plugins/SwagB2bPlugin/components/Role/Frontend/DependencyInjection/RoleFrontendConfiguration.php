<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Frontend\DependencyInjection;

use Shopware\B2B\AclRoute\Frontend\DependencyInjection\AclRouteFrontendConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoleFrontendConfiguration extends DependencyInjectionConfiguration
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
            new StoreFrontAuthenticationFrameworkConfiguration(),
            new RoleFrameworkConfiguration(),
            new AclRouteFrontendConfiguration(),
        ];
    }
}
