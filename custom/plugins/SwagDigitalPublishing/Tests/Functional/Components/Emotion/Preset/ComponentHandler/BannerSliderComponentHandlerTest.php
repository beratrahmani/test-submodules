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

namespace SwagDigitalPublishing\Tests\Functional\Components\Emotion\Preset\ComponentHandler;

use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\DependencyInjection\Container;
use SwagDigitalPublishing\Components\Emotion\Preset\ComponentHandler\BannerSliderComponentHandler;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

class BannerSliderComponentHandlerTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var BannerSliderComponentHandler
     */
    private $bannerSliderComponentHandler;

    public function test_supports_function_should_return_false()
    {
        $this->beforeTest();

        $supports = $this->bannerSliderComponentHandler->supports('my-unknown-component');

        $this->assertFalse($supports);
    }

    public function test_supports_should_succeed()
    {
        $this->beforeTest();

        $supports = $this->bannerSliderComponentHandler->supports('emotion-digital-publishing-slider');

        $this->assertTrue($supports);
    }

    public function test_import_should_return_element_unchanged()
    {
        $this->beforeTest();

        $element = require __DIR__ . '/Fixtures/BannerSliderElement.php';
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);
        unset($element['data']);

        $element = $this->bannerSliderComponentHandler->export($element, $syncData);
        $returnElement = $this->bannerSliderComponentHandler->import($element, $syncData);

        $this->assertJsonStringEqualsJsonString(json_encode($element), json_encode($returnElement));
    }

    public function test_import_should_succeed()
    {
        $this->beforeTest();

        $this->createTestBanners();
        $element = require __DIR__ . '/Fixtures/BannerSliderElement.php';
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);

        $element = $this->bannerSliderComponentHandler->export($element, $syncData);

        $syncData->set('importedAssets', []);
        $assets = $syncData->get('assets');

        foreach ($assets as &$asset) {
            $asset = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';
        }
        unset($asset);

        $syncData->set('assets', $assets);

        $returnElement = $this->bannerSliderComponentHandler->import($element, $syncData);

        $media = $this->container->get('models')->getConnection()->createQueryBuilder()
            ->select('*')
            ->from('s_media')
            ->setMaxResults(3)
            ->orderBy('id', 'DESC')
            ->execute()
            ->fetchAll();

        $this->assertRegExp('/gif-base64-/', $media[0]['name']);
        $this->assertEquals(-3, $media[0]['albumID']);

        $banner = $this->container->get('models')->getConnection()->createQueryBuilder()
            ->select('*')
            ->from('s_digital_publishing_content_banner')
            ->setMaxResults(2)
            ->orderBy('id', 'DESC')
            ->execute()
            ->fetchAll();

        $dataValue = json_decode($returnElement['data'][0]['value'], true);

        $this->assertCount(2, $dataValue);
        $this->assertEquals($banner[1]['id'], $dataValue[0]['id']);
        $this->assertEquals($banner[0]['id'], $dataValue[1]['id']);
    }

    public function test_export_should_return_element_unchanged()
    {
        $this->beforeTest();

        $element = require __DIR__ . '/Fixtures/BannerSliderElement.php';
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);
        unset($element['data']);

        $returnElement = $this->bannerSliderComponentHandler->export($element, $syncData);

        $this->assertJsonStringEqualsJsonString(json_encode($element), json_encode($returnElement));
    }

    public function test_export_should_succeed()
    {
        $this->beforeTest();

        $this->createTestBanners();

        $element = require __DIR__ . '/Fixtures/BannerSliderElement.php';
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);
        $returnElement = $this->bannerSliderComponentHandler->export($element, $syncData);

        $dataValue = json_decode($returnElement['data'][0]['value']);

        $this->assertArrayHasKey($dataValue[0], $syncData->get('banners'));
        $this->assertArrayHasKey($dataValue[1], $syncData->get('banners'));
    }

    private function beforeTest()
    {
        $this->container = Shopware()->Container();

        $mediaServiceMock = $this->createMock(MediaService::class);
        $mediaServiceMock->method('getUrl')->willReturn('data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=');

        $this->bannerSliderComponentHandler = new BannerSliderComponentHandler(
            $this->container->get('models'),
            $mediaServiceMock,
            $this->container
        );
    }

    private function createTestBanners()
    {
        $sql = file_get_contents(__DIR__ . '/../../../Fixtures/testBanners.sql');
        $this->container->get('models')->getConnection()->exec($sql);
    }
}
