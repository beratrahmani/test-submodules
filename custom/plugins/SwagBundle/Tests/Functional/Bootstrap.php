<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

use Shopware\Components\DependencyInjection\Container;
use Shopware\Kernel;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;
use SwagBundle\Tests\Functional\TestHelper\BundleTestDataAdministration;

require __DIR__ . '/../../../../../autoload.php';

class SwagBundleFunctionalTestsKernel extends Kernel
{
    /**
     * @var string
     */
    private static $plugin = 'SwagBundle';

    /**
     * @var Container
     */
    private static $swContainer;

    /**
     * Start testKernel
     */
    public static function start()
    {
        $kernel = new self(getenv('SHOPWARE_ENV') ?: 'testing', false);
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);

        /** @var Repository $repository */
        $repository = $container->get('models')->getRepository(Shop::class);

        $shop = $repository->getActiveDefault();
        $shop->registerResources();

        $_SERVER['HTTP_HOST'] = $shop->getHost();

        self::initProperties();
        self::checkPluginInstallationState();
        self::registerRequest();
        self::registerTestNamespace();
        self::registerServicesForTests();
    }

    private static function initProperties()
    {
        self::$swContainer = Shopware()->Container();
    }

    private static function checkPluginInstallationState()
    {
        $queryBuilder = self::$swContainer->get('dbal_connection')->createQueryBuilder();
        $isInstalled = (bool) $queryBuilder->select('1')
            ->from('s_core_plugins')
            ->where('`name` LIKE "SwagBundle"')
            ->andWhere('active = 1')
            ->execute()
            ->fetchColumn();

        if ($isInstalled) {
            return;
        }

        throw new \RuntimeException(sprintf('The plugin %s is not active %s', self::$plugin, PHP_EOL));
    }

    private static function registerRequest()
    {
        self::$swContainer->get('front')->setRequest(new Enlight_Controller_Request_RequestHttp());
    }

    private static function registerTestNamespace()
    {
        self::$swContainer->get('loader')->registerNamespace('SwagBundle', __DIR__ . '/../');
    }

    private static function registerServicesForTests()
    {
        self::$swContainer->set(
            'swag_bundle.test_data_administration',
            new BundleTestDataAdministration(
                self::$swContainer->get('dbal_connection')
            )
        );
    }
}

SwagBundleFunctionalTestsKernel::start();
