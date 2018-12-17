<?php declare(strict_types=1);

namespace Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Validator\DependencyInjection\ValidatorConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Bridge\DependencyInjection\StoreFrontAuthenticationBridgeConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StoreFrontAuthenticationFrameworkConfiguration extends DependencyInjectionConfiguration
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
            new AuthenticationRepositoryCollector(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new ValidatorConfiguration(),
            new StoreFrontAuthenticationBridgeConfiguration(),
        ];
    }
}
