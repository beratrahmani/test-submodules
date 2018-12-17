<?php declare(strict_types=1);

namespace Shopware\B2B\RoleBudget\Frontend\DependencyInjection;

use Shopware\B2B\Acl\Framework\DependencyInjection\AclFrameworkConfiguration;
use Shopware\B2B\Budget\Framework\DependencyInjection\BudgetFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Currency\Framework\DependencyInjection\CurrencyFrameworkConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Shopware\B2B\RoleBudget\Framework\DependencyInjection\RoleBudgetFrameworkConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoleBudgetFrontendConfiguration extends DependencyInjectionConfiguration
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
            new BudgetFrameworkConfiguration(),
            new RoleFrameworkConfiguration(),
            new AclFrameworkConfiguration(),
            new RoleBudgetFrameworkConfiguration(),
            new CurrencyFrameworkConfiguration(),
        ];
    }
}
