<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Budget\Framework\DependencyInjection\BudgetFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\OrderList\Bridge\DependencyInjection\OrderListBridgeConfiguration;
use Shopware\B2B\OrderList\Framework\OrderListContactAclTable;
use Shopware\B2B\OrderList\Framework\OrderListRoleAclTable;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderListFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @return AclTable[]
     */
    public static function createAclTables(): array
    {
        return [
            new OrderListContactAclTable(),
            new OrderListRoleAclTable(),
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
            new OrderListBridgeConfiguration(),
            new BudgetFrameworkConfiguration(),
        ];
    }
}
