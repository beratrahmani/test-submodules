<?php declare(strict_types=1);

namespace Shopware\B2B\Order\Framework\DependencyInjection;

use Shopware\B2B\AuditLog\Framework\DependencyInjection\AuditLogFrameworkConfiguration;
use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\LineItemList\Framework\DependencyInjection\LineItemListFrameworkConfiguration;
use Shopware\B2B\Order\Bridge\DependencyInjection\OrderBridgeConfiguration;
use Shopware\B2B\Order\Framework\OrderRoleAclTable;
use Shopware\B2B\OrderNumber\Framework\DependencyInjection\OrderNumberFrameworkConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public static function createAclTables(): array
    {
        return [
            new OrderRoleAclTable(),
        ];
    }

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
            new StoreFrontAuthenticationFrameworkConfiguration(),
            new AuditLogFrameworkConfiguration(),
            new ControllerConfiguration(),
            new OrderBridgeConfiguration(),
            new LineItemListFrameworkConfiguration(),
            new OrderNumberFrameworkConfiguration(),
        ];
    }
}
