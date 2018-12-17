<?php declare(strict_types=1);

namespace Shopware\B2B\Contact\Frontend\DependencyInjection;

use Shopware\B2B\AclRoute\Frontend\DependencyInjection\AclRouteFrontendConfiguration;
use Shopware\B2B\Common\DependencyInjectionConfiguration;
use Shopware\B2B\Company\Frontend\DependencyInjection\CompanyFrontendConfiguration;
use Shopware\B2B\Contact\Framework\DependencyInjection\ContactFrameworkConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContactFrontendConfiguration extends DependencyInjectionConfiguration
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
            new ContactFrameworkConfiguration(),
            new CompanyFrontendConfiguration(),
            new AclRouteFrontendConfiguration(),
        ];
    }
}
