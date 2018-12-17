<?php declare(strict_types=1);

namespace Shopware\B2B\Account\Frontend\DependencyInjection;

use Shopware\B2B\Account\Framework\DependencyInjection\AccountFrameworkConfiguration;
use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Shop\Framework\DependencyInjection\ShopFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AccountFrontendConfiguration extends DependencyInjectionConfiguration
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
            new AccountFrameworkConfiguration(),
            new ControllerConfiguration(),
            new ShopFrameworkConfiguration(),
        ];
    }
}
