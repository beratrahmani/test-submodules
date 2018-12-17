<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroupContact\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\DependencyInjection\AclFrameworkConfiguration;
use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Shopware\B2B\ContingentGroup\Framework\DependencyInjection\ContingentGroupFrameworkConfiguration;
use Shopware\B2B\ContingentGroupContact\Api\DependencyInjection\ContingentGroupContactApiConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContingentGroupContactFrameworkConfiguration extends DependencyInjectionConfiguration
{
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new AclFrameworkConfiguration(),
            new ContingentGroupContactApiConfiguration(),
            new ContingentGroupFrameworkConfiguration(),
            new ControllerConfiguration(),
            new RepositoryConfiguration(),
        ];
    }
}
