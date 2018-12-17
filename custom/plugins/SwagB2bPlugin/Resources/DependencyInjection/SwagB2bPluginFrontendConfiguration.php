<?php declare(strict_types=1);

namespace SwagB2bPlugin\Resources\DependencyInjection;

use Shopware\B2B\Account\Frontend\DependencyInjection\AccountFrontendConfiguration;
use Shopware\B2B\Budget\Frontend\DependencyInjection\BudgetFrontendConfiguration;
use Shopware\B2B\Cart\Frontend\DependencyInjection\CartFrontendConfiguration;
use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Contact\Frontend\DependencyInjection\ContactFrontendConfiguration;
use Shopware\B2B\ContingentGroup\Frontend\DependencyInjection\ContingentGroupFrontendConfiguration;
use Shopware\B2B\ContingentGroupContact\Frontend\DependencyInjection\ContingentGroupContactFrontendConfiguration;
use Shopware\B2B\ContingentRule\Frontend\DependencyInjection\ContingentRuleFrontendConfiguration;
use Shopware\B2B\Dashboard\Frontend\DependencyInjection\DashboardFrontendConfiguration;
use Shopware\B2B\FastOrder\Frontend\DependencyInjection\FastOrderFrontendConfiguration;
use Shopware\B2B\Offer\Frontend\DependencyInjection\OfferFrontendConfiguration;
use Shopware\B2B\Order\Frontend\DependencyInjection\OrderFrontendConfiguration;
use Shopware\B2B\OrderClearance\Frontend\DependencyInjection\OrderClearanceFrontendConfiguration;
use Shopware\B2B\OrderList\Frontend\DependencyInjection\OrderListFrontendConfiguration;
use Shopware\B2B\OrderNumber\Frontend\DependencyInjection\OrderNumberFrontendConfiguration;
use Shopware\B2B\OrderOrderList\Frontend\DependencyInjection\OrderOrderListFrontendConfiguration;
use Shopware\B2B\ProductName\Framework\DependencyInjection\ProductNameFrameworkConfiguration;
use Shopware\B2B\ProductSearch\Frontend\DependencyInjection\ProductSearchFrontendConfiguration;
use Shopware\B2B\Role\Frontend\DependencyInjection\RoleFrontendConfiguration;
use Shopware\B2B\RoleAddress\Frontend\DependencyInjection\RoleAddressFrontendConfiguration;
use Shopware\B2B\RoleBudget\Frontend\DependencyInjection\RoleBudgetFrontendConfiguration;
use Shopware\B2B\RoleContact\Frontend\DependencyInjection\RoleContactFrontendConfiguration;
use Shopware\B2B\RoleContingentGroup\Frontend\DependencyInjection\RoleContingentGroupFrontendConfiguration;
use Shopware\B2B\SalesRepresentative\Frontend\DependencyInjection\SalesRepresentativeFrontendConfiguration;
use Shopware\B2B\Shop\Frontend\DependencyInjection\ShopFrontendConfiguration;
use Shopware\B2B\Statistic\Frontend\DependencyInjection\StatisticFrontendConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SwagB2bPluginFrontendConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @param ContainerBuilder $containerBuilder
     * @return string[]
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/plugin-services.xml',
        ];
    }

    /**
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses(): array
    {
        return [];
    }

    /**
     * @return DependencyInjectionConfiguration[]
     */
    public function getDependingConfigurations(): array
    {
        return [
            new AccountFrontendConfiguration(),
            new BudgetFrontendConfiguration(),
            new CartFrontendConfiguration(),
            new ContactFrontendConfiguration(),
            new ContingentGroupContactFrontendConfiguration(),
            new ContingentGroupFrontendConfiguration(),
            new ContingentRuleFrontendConfiguration(),
            new ControllerConfiguration(),
            new DashboardFrontendConfiguration(),
            new FastOrderFrontendConfiguration(),
            new OfferFrontendConfiguration(),
            new OrderClearanceFrontendConfiguration(),
            new OrderFrontendConfiguration(),
            new OrderListFrontendConfiguration(),
            new OrderOrderListFrontendConfiguration(),
            new ProductNameFrameworkConfiguration(),
            new ProductSearchFrontendConfiguration(),
            new RoleContactFrontendConfiguration(),
            new RoleContingentGroupFrontendConfiguration(),
            new RoleFrontendConfiguration(),
            new ShopFrontendConfiguration(),
            new StatisticFrontendConfiguration(),
            new SalesRepresentativeFrontendConfiguration(),
            new OrderNumberFrontendConfiguration(),
            new RoleBudgetFrontendConfiguration(),
            new RoleAddressFrontendConfiguration(),
        ];
    }
}
