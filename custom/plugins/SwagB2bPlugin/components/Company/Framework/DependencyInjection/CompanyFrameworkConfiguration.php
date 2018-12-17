<?php declare(strict_types=1);

namespace Shopware\B2B\Company\Framework\DependencyInjection;

use Shopware\B2B\Acl\Framework\DependencyInjection\AclFrameworkConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompanyFrameworkConfiguration extends DependencyInjectionConfiguration
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new AclFrameworkConfiguration(),
        ];
    }
}
