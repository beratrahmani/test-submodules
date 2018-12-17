<?php declare(strict_types=1);

namespace Shopware\B2B\RoleBudget\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Budget\Framework\DependencyInjection\BudgetFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoleBudgetFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @return AclTable[]
     */
    public static function createAclTables(): array
    {
        return [
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
            new BudgetFrameworkConfiguration(),
            new RoleFrameworkConfiguration(),
        ];
    }
}
