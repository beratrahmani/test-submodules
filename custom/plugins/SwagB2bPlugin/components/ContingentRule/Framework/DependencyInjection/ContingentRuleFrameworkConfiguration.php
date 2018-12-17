<?php declare(strict_types=1);

namespace Shopware\B2B\ContingentRule\Framework\DependencyInjection;

use Shopware\B2B\Common\Controller\DependencyInjection\ControllerConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Common\Repository\DependencyInjection\RepositoryConfiguration;
use Shopware\B2B\Common\Validator\DependencyInjection\ValidatorConfiguration;
use Shopware\B2B\ContingentGroup\Framework\DependencyInjection\ContingentGroupFrameworkConfiguration;
use Shopware\B2B\Currency\Framework\DependencyInjection\CurrencyFrameworkConfiguration;
use Shopware\B2B\Shop\Framework\DependencyInjection\ShopFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContingentRuleFrameworkConfiguration extends DependencyInjectionConfiguration
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
            new ContingentRuleTypeCollector(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependingConfigurations(): array
    {
        return [
            new ContingentGroupFrameworkConfiguration(),
            new ControllerConfiguration(),
            new RepositoryConfiguration(),
            new ValidatorConfiguration(),
            new ShopFrameworkConfiguration(),
            new CurrencyFrameworkConfiguration(),
        ];
    }
}
