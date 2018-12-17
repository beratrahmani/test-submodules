<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroupContact\Api\DependencyInjection;

use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\RestApi\DependencyInjection\RestApiConfiguration;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Shopware\B2B\ContingentGroup\Framework\DependencyInjection\ContingentGroupFrameworkConfiguration;
use Shopware\B2B\ContingentGroupContact\Framework\DependencyInjection\ContingentGroupContactFrameworkConfiguration;
use Shopware\B2B\Debtor\Framework\DependencyInjection\DebtorFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContingentGroupContactApiConfiguration extends DependencyInjectionConfiguration
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
            new ContingentGroupContactFrameworkConfiguration(),
            new ContactFrameworkConfiguration(),
            new ControllerConfiguration(),
            new DebtorFrameworkConfiguration(),
            new RestApiConfiguration(),
            new ContingentGroupFrameworkConfiguration(),
        ];
    }
}
