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

namespace SwagDigitalPublishing\Tests\Functional\Components;

use Shopware\Components\DependencyInjection\Container;
use SwagDigitalPublishing\Components\ElementHandler\ButtonHandler;
use SwagDigitalPublishing\Components\ElementHandler\ImageHandler;
use SwagDigitalPublishing\Components\ElementHandler\TextHandler;
use SwagDigitalPublishing\Services\PopulateElementHandlerFactory;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class PopulateElementHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var PopulateElementHandlerFactory
     */
    private $factory;

    public function test_getHandler_text_handler()
    {
        $this->createServices();

        $textHandler = $this->factory->getHandler(['name' => 'text']);

        $this->assertInstanceOf(TextHandler::class, $textHandler);
    }

    public function test_getHandler_button_handler()
    {
        $this->createServices();

        $textHandler = $this->factory->getHandler(['name' => 'button']);

        $this->assertInstanceOf(ButtonHandler::class, $textHandler);
    }

    public function test_getHandler_image_handler()
    {
        $this->createServices();

        $textHandler = $this->factory->getHandler(['name' => 'image']);

        $this->assertInstanceOf(ImageHandler::class, $textHandler);
    }

    public function test_getHandler_not_registered_handler()
    {
        $this->createServices();

        $this->expectException(\Exception::class);

        $this->factory->getHandler(['name' => 'not_registered_handler']);
    }

    private function createServices()
    {
        $this->container = Shopware()->Container();

        $this->factory = new PopulateElementHandlerFactory(
            $this->container->get('events'),
            $this->container->get('shopware_storefront.list_product_service'),
            $this->container->get('shopware_storefront.media_service'),
            $this->container->get('legacy_struct_converter')
        );
    }
}
