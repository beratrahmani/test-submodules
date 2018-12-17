<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Frontend\DependencyInjection;

use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\LineItemList\Framework\DependencyInjection\LineItemListFrameworkConfiguration;
use Shopware\B2B\Offer\Framework\DependencyInjection\OfferFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OfferFrontendConfiguration extends DependencyInjectionConfiguration
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
            new OfferFrameworkConfiguration(),
            new LineItemListFrameworkConfiguration(),
        ];
    }
}
