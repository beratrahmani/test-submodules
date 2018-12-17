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

namespace SwagDigitalPublishing\Tests\Functional\Controllers\Widgets;

use Shopware\Components\DependencyInjection\Container;
use SwagDigitalPublishing\Services\ContentBanner;
use SwagDigitalPublishing\Services\PopulateElementHandlerFactory;
use SwagDigitalPublishing\Services\TranslationService;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

require_once __DIR__ . '/../../../../Controllers/Widgets/SwagDigitalPublishing.php';

class SwagDigitalPublishingTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    public function test_indexAction_there_should_be_nothing_in_the_view()
    {
        $this->addServices();

        $controller = new SwagDigitalPublishingWidgetTestable(Shopware()->Container());
        $controller->setToRequest('bannerId', null);
        $controller->indexAction();

        $result = $controller->View();

        $this->assertEmpty($result->viewAssign);
    }

    public function test_indexAction_there_should_be_a_banner_in_the_view()
    {
        $this->addServices();
        $this->createBanner();

        $controller = new SwagDigitalPublishingWidgetTestable(Shopware()->Container());
        $controller->setToRequest('bannerId', 3500993);
        $controller->indexAction();

        $result = $controller->View();

        $expectedSubset = [
            'id' => '3500993',
            'name' => 'Test Banner 3',
            'bgType' => 'image',
        ];

        $this->assertArraySubset($expectedSubset, $result->viewAssign['banner']);
    }

    private function createBanner()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/banners.sql');
        $this->execSql($sql);
    }

    private function addServices()
    {
        Shopware()->Container()->set(
            'swag_digital_publishing.translation_service',
            new TranslationService(
                Shopware()->Container()->get('translation')
            )
        );

        Shopware()->Container()->set(
            'digital_publishing.populate_element_handler_factory',
            new PopulateElementHandlerFactory(
                Shopware()->Container()->get('events'),
                Shopware()->Container()->get('shopware_storefront.list_product_service'),
                Shopware()->Container()->get('shopware_storefront.media_service'),
                Shopware()->Container()->get('legacy_struct_converter')
            )
        );

        Shopware()->Container()->set(
            'swag_digital_publishing.content_banner_service',
            new ContentBanner(
                Shopware()->Container()->get('models'),
                Shopware()->Container()->get('digital_publishing.translation_service'),
                Shopware()->Container()->get('shopware_storefront.list_product_service'),
                Shopware()->Container()->get('shopware_storefront.media_service'),
                Shopware()->Container()->get('digital_publishing.populate_element_handler_factory'),
                Shopware()->Container()->get('events'),
                Shopware()->Container()->get('legacy_struct_converter')
            )
        );
    }
}

class SwagDigitalPublishingWidgetTestable extends \Shopware_Controllers_Widgets_SwagDigitalPublishing
{
    /**
     * @var Container
     */
    public $container;

    /**
     * @var SwagDigitalPublishingWidgetViewMock
     */
    public $view;

    /**
     * @var SwagDigitalPublishingWidgetRequestMock
     */
    public $request;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->view = new SwagDigitalPublishingWidgetViewMock();
        $this->request = new SwagDigitalPublishingWidgetRequestMock();
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function get($string)
    {
        return $this->container->get($string);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setToRequest($key, $value)
    {
        $this->request->setParam($key, $value);
    }
}

class SwagDigitalPublishingWidgetRequestMock
{
    /**
     * @var array
     */
    public $params = [];

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getParam($key)
    {
        return $this->params[$key];
    }
}

class SwagDigitalPublishingWidgetViewMock
{
    /**
     * @var mixed[]
     */
    public $viewAssign;

    public function __construct()
    {
        $this->viewAssign = [];
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function assign($key, $value)
    {
        $this->viewAssign[$key] = $value;
    }
}
