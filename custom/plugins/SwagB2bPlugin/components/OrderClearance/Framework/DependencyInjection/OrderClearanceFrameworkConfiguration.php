<?php declare(strict_types=1);

namespace Shopware\B2B\OrderClearance\Framework\DependencyInjection;

use Shopware\B2B\AuditLog\Framework\DependencyInjection\AuditLogFrameworkConfiguration;
use Shopware\B2B\Cart\Framework\DependencyInjection\CartFrameworkConfiguration;
use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\MvcExtension\DependencyInjection\MvcExtensionConfiguration;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Shopware\B2B\OrderClearance\Bridge\DependencyInjection\OrderClearanceBridgeConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderClearanceFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public static function createAclTables(): array
    {
        return [];
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
            new CartFrameworkConfiguration(),
            new ControllerConfiguration(),
            new OrderClearanceBridgeConfiguration(),
            new AuditLogFrameworkConfiguration(),
            new ContactFrameworkConfiguration(),
            new MvcExtensionConfiguration(),
        ];
    }
}
