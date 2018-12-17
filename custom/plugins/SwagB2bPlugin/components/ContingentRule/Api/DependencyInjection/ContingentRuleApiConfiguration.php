<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Api\DependencyInjection;

use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\RestApi\DependencyInjection\RestApiConfiguration;
use Shopware\B2B\ContingentRule\Framework\DependencyInjection\ContingentRuleFrameworkConfiguration;
use Shopware\B2B\Debtor\Framework\DependencyInjection\DebtorFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContingentRuleApiConfiguration extends DependencyInjectionConfiguration
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
            new DebtorFrameworkConfiguration(),
            new ControllerConfiguration(),
            new RestApiConfiguration(),
            new ContingentRuleFrameworkConfiguration(),
        ];
    }
}
