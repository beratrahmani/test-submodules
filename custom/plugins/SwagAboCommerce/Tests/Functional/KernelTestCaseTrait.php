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

namespace SwagAboCommerce\Tests\Functional;

use Enlight_Controller_Front;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_Response;
use Enlight_Controller_Response_ResponseHttp;
use Enlight_Controller_Response_ResponseTestCase;
use Shopware\Kernel;
use Shopware\Models\Shop\Shop;

trait KernelTestCaseTrait
{
    /**
     * @var Enlight_Controller_Response_Response
     */
    protected $_response;

    /**
     * @var Enlight_Controller_Request_RequestTestCase
     */
    protected $_request;

    /**
     * @var Enlight_Controller_Front
     */
    protected $_front;

    /**
     * @var Kernel
     */
    private static $kernel;

    /**
     * Retrieve test case request object
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function Request()
    {
        if (null === $this->_request) {
            $this->_request = new Enlight_Controller_Request_RequestTestCase();
        }

        return $this->_request;
    }

    /**
     * @return Enlight_Controller_Response_ResponseHttp
     */
    public function Response()
    {
        if (null === $this->_response) {
            $this->_response = new Enlight_Controller_Response_ResponseTestCase();
        }

        return $this->_response;
    }

    /**
     * @return Enlight_Controller_Front
     */
    public function Front()
    {
        if (null === $this->_front) {
            $this->_front = Shopware()->Container()->get('Front');
        }

        return $this->_front;
    }

    /**
     * @return Kernel
     */
    protected static function getKernel()
    {
        if (!self::$kernel) {
            self::bootKernelBefore();
        }

        return self::$kernel;
    }

    /**
     * @before
     */
    protected static function bootKernelBefore()
    {
        if (self::$kernel instanceof Kernel) {
            return;
        }
        self::$kernel = new Kernel(getenv('SHOPWARE_ENV') ?: 'testing', true);
        self::$kernel->boot();

        self::$kernel->getContainer()->get('dbal_connection')->beginTransaction();

        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Container()->get('models')->getRepository(Shop::class);

        $shop = $repository->getActiveDefault();
        $shop->registerResources();
    }

    /**
     * @after
     */
    protected static function destroyKernelAfter()
    {
        self::$kernel->getContainer()->get('dbal_connection')->rollBack();

        self::$kernel = null;
        gc_collect_cycles();
        Shopware(new EmptyShopwareApplication());
    }

    /**
     * @return \Shopware\Components\DependencyInjection\Container
     */
    protected static function getContainer()
    {
        if (self::$kernel === null) {
            self::bootKernelBefore();
        }

        return self::$kernel->getContainer();
    }

    /**
     * @param string $sql
     */
    protected function execSql($sql)
    {
        self::getContainer()->get('dbal_connection')->exec($sql);
    }

    /**
     * @return \Doctrine\DBAL\Connection|mixed
     */
    protected function getDbalConnection()
    {
        return Shopware()->Container()->get('dbal_connection');
    }
}

class EmptyShopwareApplication
{
    public function __call($name, $arguments)
    {
        throw new \RuntimeException('Restricted to call ' . $name . ' because you should not have a test kernel in this test case.');
    }
}
