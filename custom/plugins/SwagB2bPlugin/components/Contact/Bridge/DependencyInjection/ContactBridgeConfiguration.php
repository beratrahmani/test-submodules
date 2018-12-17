<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Bridge\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\StoreFrontAuthentication\Framework\DependencyInjection\StoreFrontAuthenticationFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContactBridgeConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        $serviceFiles = [
            __DIR__ . '/bridge-services.xml',
        ];

        if ($containerBuilder->has('customer_search.dbal.number_search')) {
            $serviceFiles[] = __DIR__ . '/bridge-services-53.xml';
        }

        return $serviceFiles;
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
            new StoreFrontAuthenticationFrameworkConfiguration(),
        ];
    }
}
