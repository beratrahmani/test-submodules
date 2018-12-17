<?php declare(strict_types=1);

namespace Shopware\B2B\Address\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Address\Bridge\DependencyInjection\AddressBridgeConfiguration;
use Shopware\B2B\Address\Framework\AddressContactAclTable;
use Shopware\B2B\Address\Framework\AddressRoleAclTable;
use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\NestedSetBridge\DependencyInjection\NestedSetBridgeConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Shopware\B2B\Common\Validator\DependencyInjection\ValidatorConfiguration;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Shopware\B2B\Shop\Framework\DependencyInjection\ShopFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddressFrameworkConfiguration extends DependencyInjectionConfiguration
{
    /**
     * @return AclTable[]
     */
    public static function createAclTables(): array
    {
        return [
            new AddressContactAclTable(),
            new AddressRoleAclTable(),
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
            new AddressBridgeConfiguration(),
            new ControllerConfiguration(),
            new ValidatorConfiguration(),
            new RepositoryConfiguration(),
            new ShopFrameworkConfiguration(),
            new ContactFrameworkConfiguration(),
            new RoleFrameworkConfiguration(),
            new NestedSetBridgeConfiguration(),
        ];
    }
}
