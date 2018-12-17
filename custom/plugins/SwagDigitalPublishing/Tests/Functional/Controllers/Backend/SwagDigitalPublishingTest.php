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

namespace ShopwarePlugins\SwagDigitalPublishing\tests\Functional\Controllers\Backend;

require_once __DIR__ . '/../../../../Controllers/Backend/SwagDigitalPublishing.php';

use Shopware\Components\DependencyInjection\Container;
use SwagDigitalPublishing\Services\ContentBanner;
use SwagDigitalPublishing\Services\PopulateElementHandlerFactory;
use SwagDigitalPublishing\Services\TranslationService;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class SwagDigitalPublishingTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    /**
     * @var Container
     */
    public $container;

    public function test_previewAction_with_json_banner()
    {
        $this->beforeTest();
        $this->createBanner();

        $json = '{"id":3500993,"name":"Test Banner 3","bgType":"image","bgOrientation":"top center","bgMode":"cover","bgColor":"","mediaId":440,"layers":[{"id":376803,"contentBannerID":3500993,"position":0,"label":"Test Ebene 1","width":"auto","height":"auto","marginTop":0,"marginRight":0,"marginBottom":53,"marginLeft":377,"borderRadius":30,"orientation":"center center","bgColor":"#FA1240","link":"","elements":[{"id":4996384,"layerID":376803,"position":0,"name":"text","label":"Text","payload":"{\"text\":\"Lorem ipsum dolor sit amet\",\"type\":\"h1\",\"font\":\"Open Sans\",\"fontsize\":16,\"lineHeight\":1,\"fontcolor\":\"#FFFFFF\",\"textfield-2854-inputEl\":\"\",\"orientation\":\"left\",\"fontweight\":false,\"fontstyle\":false,\"underline\":false,\"uppercase\":false,\"shadowColor\":\"\",\"textfield-2858-inputEl\":\"\",\"shadowOffsetX\":0,\"shadowOffsetY\":0,\"shadowBlur\":0,\"paddingTop\":10,\"paddingLeft\":10,\"paddingChain\":true,\"paddingRight\":10,\"paddingBottom\":10,\"class\":\"\"}","text":"Lorem ipsum dolor sit amet","type":"h1","font":"Open Sans","fontsize":16,"lineHeight":1,"fontcolor":"#FFFFFF","textfield-2854-inputEl":"","orientation":"left","fontweight":false,"fontstyle":false,"underline":false,"uppercase":false,"shadowColor":"","textfield-2858-inputEl":"","shadowOffsetX":0,"shadowOffsetY":0,"shadowBlur":0,"paddingTop":10,"paddingLeft":10,"paddingChain":true,"paddingRight":10,"paddingBottom":10,"class":""},{"id":5996385,"layerID":376803,"position":1,"name":"button","label":"Button","payload":"{\"text\":\"Zum Kleid\",\"type\":\"standard\",\"target\":\"_self\",\"link-search\":\"\",\"link\":\"https://www.google.de/\",\"orientation\":\"center\",\"paddingTop\":5,\"paddingLeft\":0,\"paddingChain\":false,\"paddingRight\":0,\"paddingBottom\":15,\"autoSize\":true,\"width\":200,\"height\":38,\"fontsize\":14,\"class\":\"\"}","text":"Zum Kleid","type":"standard","target":"_self","link-search":"","link":"https://www.google.de/","orientation":"center","paddingTop":5,"paddingLeft":0,"paddingChain":false,"paddingRight":0,"paddingBottom":15,"autoSize":true,"width":200,"height":38,"fontsize":14,"class":""}]},{"id":476804,"contentBannerID":3500993,"position":1,"label":"Test Ebene 2","width":"auto","height":"auto","marginTop":0,"marginRight":0,"marginBottom":0,"marginLeft":0,"borderRadius":0,"orientation":"center center","bgColor":"","link":"https://www.google.de/","elements":[{"id":6996386,"layerID":476804,"position":0,"name":"image","label":"Bild","payload":"{\"mediaId\":439,\"alt\":\"\",\"maxWidth\":100,\"maxHeight\":100,\"orientation\":\"left\",\"paddingTop\":200,\"paddingLeft\":400,\"paddingChain\":false,\"paddingRight\":0,\"paddingBottom\":0,\"class\":\"\"}","mediaId":439,"alt":"","maxWidth":100,"maxHeight":100,"orientation":"left","paddingTop":200,"paddingLeft":400,"paddingChain":false,"paddingRight":0,"paddingBottom":0,"class":""}]}]}';

        $controller = new SwagDigitalPublishingMock($this->container);
        $controller->setToRequest('banner', $json);
        $controller->previewAction();

        $resultStringSuffix = 'Resources/views/';
        $expectedSubset = [
            'id' => 3500993,
            'name' => 'Test Banner 3',
            'bgType' => 'image',
        ];

        $this->assertStringEndsWith($resultStringSuffix, $controller->getView()->directories[0]);
        $this->assertArraySubset($expectedSubset, $controller->getView()->viewAssign['banner']);
    }

    public function test_previewAction_with_id()
    {
        $this->beforeTest();
        $this->createBanner();

        $controller = new SwagDigitalPublishingMock($this->container);
        $controller->setToRequest('bannerId', 3500993);
        $controller->previewAction();

        $resultStringSuffix = 'Resources/views/';
        $expectedSubset = [
            'id' => 3500993,
            'name' => 'Test Banner 3',
            'bgType' => 'image',
        ];

        $this->assertStringEndsWith($resultStringSuffix, $controller->getView()->directories[0]);
        $this->assertArraySubset($expectedSubset, $controller->getView()->viewAssign['banner']);
    }

    public function test_getWhitelistedCSRFActions()
    {
        $this->beforeTest();

        $controller = new SwagDigitalPublishingMock(Shopware()->Container());

        $expectedSubset = [
            'preview',
        ];

        $result = $controller->getWhitelistedCSRFActions();

        $this->assertArraySubset($expectedSubset, $result);
    }

    private function createBanner()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/banners.sql');
        $this->execSql($sql);
    }

    private function beforeTest()
    {
        $this->container = Shopware()->Container();
        $this->addServices();
    }

    private function addServices()
    {
        $this->container->set(
            'digital_publishing.translation_service',
            new TranslationService(
                Shopware()->Container()->get('translation')
            )
        );

        $this->container->set(
            'digital_publishing.populate_element_handler_factory',
            new PopulateElementHandlerFactory(
                $this->container->get('events'),
                $this->container->get('shopware_storefront.list_product_service'),
                $this->container->get('shopware_storefront.media_service'),
                $this->container->get('legacy_struct_converter')
            )
        );

        $this->container->set(
            'digital_publishing.content_banner_service',
            new ContentBanner(
                $this->container->get('models'),
                $this->container->get('digital_publishing.translation_service'),
                $this->container->get('shopware_storefront.list_product_service'),
                $this->container->get('shopware_storefront.media_service'),
                $this->container->get('digital_publishing.populate_element_handler_factory'),
                $this->container->get('events'),
                $this->container->get('legacy_struct_converter')
            )
        );
    }
}

class SwagDigitalPublishingMock extends \Shopware_Controllers_Backend_SwagDigitalPublishing
{
    /**
     * @var Container
     */
    public $container;

    /**
     * @var SwagContentBannerViewMock
     */
    public $view;

    /**
     * @var string
     */
    public $model;

    /**
     * @var SwagDigitalPublishingRequestMock
     */
    public $request;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->view = new SwagDigitalPublishingViewMock();
        $this->request = new SwagDigitalPublishingRequestMock();
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

    /**
     * @return SwagDigitalPublishingViewMock
     */
    public function getView()
    {
        return $this->view;
    }
}

class SwagDigitalPublishingRequestMock
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
     * @param null   $default
     *
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }

        return $default;
    }
}

class SwagDigitalPublishingViewMock
{
    /**
     * @var mixed[]
     */
    public $viewAssign;

    /**
     * @var string[]
     */
    public $directories;

    public function __construct()
    {
        $this->viewAssign = [];
        $this->directories = [];
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function assign($key, $value)
    {
        $this->viewAssign[$key] = $value;
    }

    /**
     * @param string $directory
     */
    public function addTemplateDir($directory)
    {
        $this->directories[] = $directory;
    }

    public function loadTemplate()
    {
        // DO NOTHING
    }
}
