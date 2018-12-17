<?php declare(strict_types=1);

namespace Shopware\B2B\RoleAddress\Api\DependencyInjection;

use Shopware\B2B\Address\Framework\DependencyInjection\AddressFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoleAddressApiConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/api-services.xml',
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
            new RoleFrameworkConfiguration(),
            new AddressFrameworkConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
        ];
    }
}
