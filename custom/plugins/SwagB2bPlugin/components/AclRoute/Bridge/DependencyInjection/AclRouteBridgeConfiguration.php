<?php declare(strict_types=1);

namespace Shopware\B2B\AclRoute\Bridge\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AclRouteBridgeConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/bridge-services.xml',
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
        return [];
    }
}
