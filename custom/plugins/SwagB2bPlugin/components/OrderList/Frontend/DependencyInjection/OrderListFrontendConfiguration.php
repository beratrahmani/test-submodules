<?php declare(strict_types=1);

namespace Shopware\B2B\OrderList\Frontend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Shopware\B2B\OrderList\Framework\DependencyInjection\OrderListFrameworkConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OrderListFrontendConfiguration extends DependencyInjectionConfiguration
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
            new RoleFrameworkConfiguration(),
            new ContactFrameworkConfiguration(),
            new OrderListFrameworkConfiguration(),
            new StoreFrontAuthenticationFrameworkConfiguration(),
        ];
    }
}
