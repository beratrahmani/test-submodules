<?php declare(strict_types=1);

namespace Shopware\B2B\Common\File\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FileConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/file-service.xml',
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
        return [];
    }
}
