<?php declare(strict_types=1);

namespace SwagB2bPlugin\Resources\DependencyInjection;

use Shopware\B2B\AclRoute\Api\DependencyInjection\AclRouteApiConfiguration;
use Shopware\B2B\Address\Api\DependencyInjection\AddressApiConfiguration;
use Shopware\B2B\Address\Frontend\DependencyInjection\AddressFrontendConfiguration;
use Shopware\B2B\Budget\Api\DependencyInjection\BudgetApiConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Contact\Api\DependencyInjection\ContactApiConfiguration;
use Shopware\B2B\ContingentGroup\Api\DependencyInjection\ContingentGroupApiConfiguration;
use Shopware\B2B\ContingentRule\Api\DependencyInjection\ContingentRuleApiConfiguration;
use Shopware\B2B\InStock\Api\DependencyInjection\InStockApiConfiguration;
use Shopware\B2B\Offer\Api\DependencyInjection\OfferApiConfiguration;
use Shopware\B2B\OrderList\Api\DependencyInjection\OrderListApiConfiguration;
use Shopware\B2B\Price\Api\DependencyInjection\PriceApiConfiguration;
use Shopware\B2B\Role\Api\DependencyInjection\RoleApiConfiguration;
use Shopware\B2B\RoleAddress\Api\DependencyInjection\RoleAddressApiConfiguration;
use Shopware\B2B\RoleBudget\Api\DependencyInjection\RoleBudgetApiConfiguration;
use Shopware\B2B\RoleContact\Api\DependencyInjection\RoleContactApiConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SwagB2bPluginApiConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @param ContainerBuilder $containerBuilder
     * @return string[]
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [];
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
            new AclRouteApiConfiguration(),
            new AddressApiConfiguration(),
            new AddressFrontendConfiguration(),
            new RoleContactApiConfiguration(),
            new ContactApiConfiguration(),
            new ContingentRuleApiConfiguration(),
            new OrderListApiConfiguration(),
            new PriceApiConfiguration(),
            new RoleApiConfiguration(),
            new BudgetApiConfiguration(),
            new ContingentGroupApiConfiguration(),
            new InStockApiConfiguration(),
            new RoleBudgetApiConfiguration(),
            new OfferApiConfiguration(),
            new RoleAddressApiConfiguration(),
        ];
    }
}
