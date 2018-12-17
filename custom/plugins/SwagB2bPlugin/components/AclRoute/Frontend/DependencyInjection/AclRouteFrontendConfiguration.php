<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Frontend\DependencyInjection;

use Shopware\B2B\Acl\Framework\DependencyInjection\AclFrameworkConfiguration;
use Shopware\B2B\AclRoute\Framework\DependencyInjection\AclRouteFrameworkConfiguration;
use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\MvcExtension\DependencyInjection\MvcExtensionConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AclRouteFrontendConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [];
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
            new AclRouteFrameworkConfiguration(),
            new AclFrameworkConfiguration(),
            new ControllerConfiguration(),
            new MvcExtensionConfiguration(),
            new RepositoryConfiguration(),
        ];
    }
}
