<?php declare(strict_types=1);

namespace Shopware\B2B\Account\Framework\DependencyInjection;

use Shopware\B2B\Account\Bridge\DependencyInjection\AccountBridgeConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AccountFrameworkConfiguration extends DependencyInjectionConfiguration
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
            new AccountBridgeConfiguration(),
        ];
    }
}
