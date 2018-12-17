<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentGroup\Api\DependencyInjection;

use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\RestApi\DependencyInjection\RestApiConfiguration;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Shopware\B2B\ContingentGroup\Framework\DependencyInjection\ContingentGroupFrameworkConfiguration;
use Shopware\B2B\ContingentGroupContact\Framework\DependencyInjection\ContingentGroupContactFrameworkConfiguration;
use Shopware\B2B\Debtor\Framework\DependencyInjection\DebtorFrameworkConfiguration;
use Shopware\B2B\Role\Framework\DependencyInjection\RoleFrameworkConfiguration;
use Shopware\B2B\RoleContingentGroup\Framework\DependencyInjection\RoleContingentGroupFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContingentGroupApiConfiguration extends DependencyInjectionConfiguration
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
            new RoleFrameworkConfiguration(),
            new RoleContingentGroupFrameworkConfiguration(),
            new ContingentGroupContactFrameworkConfiguration(),
            new ContactFrameworkConfiguration(),
            new DebtorFrameworkConfiguration(),
            new ControllerConfiguration(),
            new RestApiConfiguration(),
            new ContingentGroupFrameworkConfiguration(),
        ];
    }
}
