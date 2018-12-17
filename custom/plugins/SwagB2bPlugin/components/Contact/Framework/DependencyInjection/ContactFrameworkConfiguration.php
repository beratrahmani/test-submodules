<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\AclTable;
use Shopware\B2B\Acl\Framework\DependencyInjection\AclFrameworkConfiguration;
use Shopware\B2B\Address\Framework\DependencyInjection\AddressFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Shopware\B2B\Common\Validator\DependencyInjection\ValidatorConfiguration;
use Shopware\B2B\Company\Framework\DependencyInjection\CompanyFrameworkConfiguration;
use Shopware\B2B\Contact\Bridge\DependencyInjection\ContactBridgeConfiguration;
use Shopware\B2B\Contact\Framework\AclRouteAclTable;
use Shopware\B2B\Contact\Framework\ContactContactAclTable;
use Shopware\B2B\Contact\Framework\ContactRoleAclTable;
use Shopware\B2B\Debtor\Framework\DependencyInjection\DebtorFrameworkConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContactFrameworkConfiguration extends DependencyInjectionConfiguration
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
    public static function createContactAclTables(): array
    {
        return [
            new ContactRoleAclTable(),
            new ContactContactAclTable(),
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
            new ContactAccessWriterCollector(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new ContactBridgeConfiguration(),
            new DebtorFrameworkConfiguration(),
            new AddressFrameworkConfiguration(),
            new RepositoryConfiguration(),
            new ValidatorConfiguration(),
            new AclFrameworkConfiguration(),
            new CompanyFrameworkConfiguration(),
            new RoleFrameworkConfiguration(),
        ];
    }
}
