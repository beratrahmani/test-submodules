<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Backend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\LineItemList\Framework\DependencyInjection\LineItemListFrameworkConfiguration;
use Shopware\B2B\Offer\Framework\DependencyInjection\OfferFrameworkConfiguration;
use Shopware\B2B\Order\Framework\DependencyInjection\OrderFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OfferBackendConfiguration extends DependencyInjectionConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getServiceFiles(ContainerBuilder $containerBuilder): array
    {
        return [
            __DIR__ . '/backend-services.xml',
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
            new OfferFrameworkConfiguration(),
            new LineItemListFrameworkConfiguration(),
            new OrderFrameworkConfiguration(),
        ];
    }
}
