<?php declare(strict_types=1);

namespace Shopware\B2B\Price\Api\DependencyInjection;

use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\RestApi\DependencyInjection\RestApiConfiguration;
use Shopware\B2B\Price\Framework\DependencyInjection\PriceFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PriceApiConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/api-services.xml',
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
            new ControllerConfiguration(),
            new RestApiConfiguration(),
            new PriceFrameworkConfiguration(),
        ];
    }
}
