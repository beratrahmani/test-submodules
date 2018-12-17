<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Framework\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Offer\Bridge\DependencyInjection\OfferBridgeConfiguration;
use Shopware\B2B\Order\Framework\DependencyInjection\OrderFrameworkConfiguration;
use Shopware\B2B\ProductName\Framework\DependencyInjection\ProductNameFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OfferFrameworkConfiguration extends DependencyInjectionConfiguration
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
            new OfferBridgeConfiguration(),
            new OrderFrameworkConfiguration(),
            new ProductNameFrameworkConfiguration(),
        ];
    }
}
