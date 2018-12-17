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

use ReflectionClass;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\EmotionBundle\Struct\ElementConfig;
use Shopware\Bundle\EmotionBundle\Struct\ElementData;
use Shopware\Bundle\EmotionBundle\Struct\Library\Component;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagDigitalPublishing\Components\BannerSliderElementHandler;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class BannerSliderElementHandlerTest extends \PHPUnit_Framework_TestCase
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
        $bannerElementHandler = $this->getBannerSliderElementHandler();

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
            [$this->createElement('emotion/digital/publishing/slider'), false],
            [$this->createElement('Emotion-Digital-Publishing-Slider'), false],
            [$this->createElement('emotion-digital-publishing-slider-not-supported'), false],
            [$this->createElement('emotion-digital-publishing-slider'), true],
        ];
    }

    public function test_handle()
    {
        $this->installTestBanners();

        $bannerElementHandler = $this->getBannerSliderElementHandler();

        $element = $this->createElement(
            'emotion-digital-publishing-slider',
            [
                'digital_publishing_slider_payload' => json_encode([
                    ['id' => 1],
                    ['id' => 2],
                    ['id' => 3],
                ]),
            ]
        );

        $bannerElementHandler->handle(
            $this->getResolvedDataCollection(),
            $element,
            $this->getShopContext()
        );

        $expectedResult = [
            [
                'id' => '1',
                'name' => 'NB1',
                'bgType' => 'image',
                'bgOrientation' => 'center center',
                'bgMode' => 'cover',
                'bgColor' => '',
                'mediaId' => '553',
                'layers' => [],
            ],
            [
                'id' => '2',
                'name' => 'NB2',
                'bgType' => 'image',
                'bgOrientation' => 'top center',
                'bgMode' => 'cover',
                'bgColor' => '',
                'mediaId' => '561',
                'layers' => [],
            ],
            [
                'id' => '3',
                'name' => 'nb3',
                'bgType' => 'color',
                'bgOrientation' => 'center center',
                'bgMode' => 'cover',
                'bgColor' => '#00EF42',
                'mediaId' => null,
                'layers' => [],
            ],
        ];

        $result = $element->getData();
        $banners = $result->get('banners');

        $this->assertInstanceOf(ElementData::class, $result);
        $this->assertArraySubset($expectedResult, $banners);
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

        $reflectioClass = new ReflectionClass(Element::class);
        $properety = $reflectioClass->getProperty('data');
        $properety->setAccessible(true);
        $properety->setValue($element, $data);

        return $element;
    }

    /**
     * @return BannerSliderElementHandler
     */
    private function getBannerSliderElementHandler()
    {
        return new BannerSliderElementHandler(
            Shopware()->Container()->get('digital_publishing.content_banner_service')
        );
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
}
