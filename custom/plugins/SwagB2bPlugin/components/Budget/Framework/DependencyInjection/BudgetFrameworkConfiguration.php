<?php declare(strict_types=1);

namespace Shopware\B2B\Budget\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Acl\Framework\DependencyInjection\AclFrameworkConfiguration;
use Shopware\B2B\Budget\Bridge\DependencyInjection\BudgetBridgeConfiguration;
use Shopware\B2B\Budget\Framework\BudgetContactAclTable;
use Shopware\B2B\Budget\Framework\BudgetRoleAclTable;
use Shopware\B2B\Cart\Framework\DependencyInjection\CartFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Shopware\B2B\Currency\Framework\DependencyInjection\CurrencyFrameworkConfiguration;
use Shopware\B2B\ProductName\Framework\DependencyInjection\ProductNameFrameworkConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Shopware\B2B\Shop\Framework\DependencyInjection\ShopFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BudgetFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @return AclTable[]
     */
    public static function createAclTables(): array
    {
        return [
            new BudgetContactAclTable(),
            new BudgetRoleAclTable(),
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
            new BudgetBridgeConfiguration(),
            new ShopFrameworkConfiguration(),
            new CurrencyFrameworkConfiguration(),
            new CartFrameworkConfiguration(),
            new RoleFrameworkConfiguration(),
            new AclFrameworkConfiguration(),
            new ContactFrameworkConfiguration(),
            new ProductNameFrameworkConfiguration(),
        ];
    }
}
