<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Acl\Framework\DependencyInjection\AclFrameworkConfiguration;
use Shopware\B2B\Cart\Framework\DependencyInjection\CartFrameworkConfiguration;
use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Shopware\B2B\Common\Validator\DependencyInjection\ValidatorConfiguration;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupContactAclTable;
use Shopware\B2B\ContingentGroup\Framework\ContingentGroupRoleAclTable;
use Shopware\B2B\ProductName\Framework\DependencyInjection\ProductNameFrameworkConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContingentGroupFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @return AclTable[]
     */
    public static function createAclTables(): array
    {
        return [
            new ContingentGroupContactAclTable(),
            new ContingentGroupRoleAclTable(),
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
            new AclFrameworkConfiguration(),
            new CartFrameworkConfiguration(),
            new ControllerConfiguration(),
            new RepositoryConfiguration(),
            new ValidatorConfiguration(),
            new ProductNameFrameworkConfiguration(),
            new RoleFrameworkConfiguration(),
        ];
    }
}
