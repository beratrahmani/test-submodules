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

namespace SwagProductAdvisor\Tests;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContext;

/**
 * Class TestCase
 */
class TestCase extends \Shopware\Components\Test\Plugin\TestCase
{
    /**
     * @var Helper
     */
    protected static $helper;

    /**
     * @var \Enlight_Controller_Request_RequestHttp
     */
    protected static $request;

    /**
     * @var ProductContext
     */
    protected static $productContext;

    protected function setUp()
    {
        parent::setUp();
        $this::$helper = new Helper();
        $this::$request = $this::$helper->createRequest();

        /** @var ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');
        $this::$productContext = $contextService->getProductContext();
    }

    /**
     * delete unnecessary data
     */
    protected function tearDown()
    {
        $this::$helper->cleanUp();
    }
}
