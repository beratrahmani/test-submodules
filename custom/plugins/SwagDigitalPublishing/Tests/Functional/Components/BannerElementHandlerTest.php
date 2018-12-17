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

namespace ShopwarePlugins\SwagDigitalPublishing\tests\Functional\Components;

use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\EmotionBundle\Struct\ElementConfig;
use Shopware\Bundle\EmotionBundle\Struct\ElementData;
use Shopware\Bundle\EmotionBundle\Struct\Library\Component;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagDigitalPublishing\Components\BannerElementHandler;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class BannerElementHandlerTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    /**
     * @dataProvider test_supports_DataProvider
     *
     * @param Element $element
     * @param bool    $expectedResult
     */
    public function test_supports(Element $element, $expectedResult)
    {
        $bannerElementHandler = $this->getBannerElementHandler();

        $result = $bannerElementHandler->supports($element);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function test_supports_DataProvider()
    {
        return [
            [$this->createElement(null), false],
            [$this->createElement(''), false],
            [$this->createElement('notSupported'), false],
            [$this->createElement('otherNotSupported'), false],
            [$this->createElement('emotion/digital/publishing'), false],
            [$this->createElement('Emotion-Digital-Publishing'), false],
            [$this->createElement('Emotion-Digital-Publishing-not-supported'), false],
            [$this->createElement('emotion-digital-publishing'), true],
        ];
    }

    public function test_handle()
    {
        $this->installTestBanners();

        $bannerElementHandler = $this->getBannerElementHandler();

        $element = $this->createElement('emotion-digital-publishing', ['digital_publishing_banner_id' => 1]);

        $bannerElementHandler->handle(
            $this->getResolvedDataCollection(),
            $element,
            $this->getShopContext()
        );

        $expectedSubset = [
            'id' => '1',
            'name' => 'NB1',
            'bgType' => 'image',
            'bgOrientation' => 'center center',
            'bgMode' => 'cover',
            'bgColor' => '',
            'mediaId' => '553',
            'layers' => [],
        ];

        $result = $element->getData();
        $bannerArray = $result->get('contentBanner');

        $this->assertInstanceOf(ElementData::class, $result);
        $this->assertArraySubset($expectedSubset, $bannerArray);
    }

    private function installTestBanners()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/elementHandlerTestBanner.sql');

        Shopware()->Container()->get('dbal_connection')->exec($sql);
    }

    /**
     * @return ResolvedDataCollection
     */
    private function getResolvedDataCollection()
    {
        return new ResolvedDataCollection();
    }

    /**
     * @return ShopContextInterface
     */
    private function getShopContext()
    {
        return Shopware()->Container()->get('shopware_storefront.context_service')
            ->createShopContext(1, 1, 'EK');
    }

    /**
     * @return BannerElementHandler
     */
    private function getBannerElementHandler()
    {
        return new BannerElementHandler(
            Shopware()->Container()->get('digital_publishing.content_banner_service')
        );
    }

    /**
     * @param string $type
     *
     * @return Element
     */
    private function createElement($type, array $elementData = [])
    {
        $element = new Element();
        $component = new Component();
        $config = new ElementConfig();
        $data = new ElementData();

        foreach ($elementData as $key => $value) {
            $data->set($key, $value);
        }

        $component->setType($type);

        $element->setComponent($component);
        $element->setConfig($config);

        $reflectioClass = new \ReflectionClass(Element::class);
        $properety = $reflectioClass->getProperty('data');
        $properety->setAccessible(true);
        $properety->setValue($element, $data);

        return $element;
    }
}
