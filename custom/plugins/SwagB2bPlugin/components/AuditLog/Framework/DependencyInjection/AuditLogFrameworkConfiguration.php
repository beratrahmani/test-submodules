<?php declare(strict_types=1);

namespace Shopware\B2B\AuditLog\Framework\DependencyInjection;

use Shopware\B2B\AuditLog\Bridge\DependencyInjection\AuditLogBridgeConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Shopware\B2B\Currency\Framework\DependencyInjection\CurrencyFrameworkConfiguration;
use Shopware\B2B\ProductName\Framework\DependencyInjection\ProductNameFrameworkConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AuditLogFrameworkConfiguration extends DependencyInjectionConfiguration
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
            new RepositoryConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
            new AuditLogBridgeConfiguration(),
            new CurrencyFrameworkConfiguration(),
            new ProductNameFrameworkConfiguration(),
        ];
    }
}
