<?php declare(strict_types=1);

namespace Shopware\B2B\Offer\Api\DependencyInjection;

use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\RestApi\DependencyInjection\RestApiConfiguration;
use Shopware\B2B\Debtor\Framework\DependencyInjection\DebtorFrameworkConfiguration;
use Shopware\B2B\Offer\Framework\DependencyInjection\OfferFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OfferApiConfiguration extends DependencyInjectionConfiguration
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
            new OfferFrameworkConfiguration(),
            new ControllerConfiguration(),
            new DebtorFrameworkConfiguration(),
            new RestApiConfiguration(),
        ];
    }
}
