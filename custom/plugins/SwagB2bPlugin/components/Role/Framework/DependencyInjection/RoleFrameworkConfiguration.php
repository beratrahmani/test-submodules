<?php declare(strict_types=1);

namespace Shopware\B2B\Role\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Acl\Framework\DependencyInjection\AclFrameworkConfiguration;
use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\NestedSetBridge\DependencyInjection\NestedSetBridgeConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Shopware\B2B\Common\Validator\DependencyInjection\ValidatorConfiguration;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Shopware\B2B\Role\Framework\AclRouteAclTable;
use Shopware\B2B\Role\Framework\RoleContactAclTable;
use Shopware\B2B\Role\Framework\RoleRoleAclTable;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoleFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @return AclTable[]
     */
    public static function createAclTables(): array
    {
        return [
            new AclRouteAclTable(),
        ];
    }

    /**
     * @return AclTable[]
     */
    public static function createRoleAclTables()
    {
        return [
            new RoleRoleAclTable(),
            new RoleContactAclTable(),
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
        return [
            new RoleRemoveDependencyValidatorCollector(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new ControllerConfiguration(),
            new RepositoryConfiguration(),
            new ValidatorConfiguration(),
            new NestedSetBridgeConfiguration(),
            new AclFrameworkConfiguration(),
            new ContactFrameworkConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
        ];
    }
}
