<?php declare(strict_types=1);

namespace SwagB2bPlugin;

use Shopware\B2B\Common\B2BContainerBuilder;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use SwagB2bPlugin\Resources\DependencyInjection\SwagB2bPluginApiConfiguration;
use SwagB2bPlugin\Resources\DependencyInjection\SwagB2bPluginBackendConfiguration;
use SwagB2bPlugin\Resources\DependencyInjection\SwagB2bPluginFrontendConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SwagB2bPlugin extends Plugin
{
    /**
     * @return bool
     */
    public static function isPackage(): bool
    {
        return file_exists(self::getPackageVendorAutoload());
    }

    /**
     * @return string
     */
    public static function getPackageVendorAutoload(): string
    {
        return __DIR__ . '/vendor/autoload.php';
    }

    /**
     * @return string
     */
    public static function getMigrationPath(): string
    {
        if (self::isPackage()) {
            return __DIR__ . '/components/Common/Version/Migration';
        }

        return __DIR__ . '/../../components/Common/Version/Migration';
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context)
    {
        (new SetUp())->setUp(self::getMigrationPath());

        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context)
    {
        (new SetUp())->setUp(self::getMigrationPath());

        $context->scheduleClearCache(UpdateContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $containerBuilder = B2BContainerBuilder::create();
        $containerBuilder->addConfiguration(new SwagB2bPluginApiConfiguration());
        $containerBuilder->addConfiguration(new SwagB2bPluginFrontendConfiguration());
        $containerBuilder->addConfiguration(new SwagB2bPluginBackendConfiguration());
        $containerBuilder->registerConfigurations($container);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);
    }
}

if (SwagB2bPlugin::isPackage()) {
    require_once SwagB2bPlugin::getPackageVendorAutoload();
}
