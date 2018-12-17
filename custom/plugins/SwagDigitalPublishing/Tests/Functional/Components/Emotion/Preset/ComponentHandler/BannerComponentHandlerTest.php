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
use SwagDigitalPublishing\Components\Emotion\Preset\ComponentHandler\BannerComponentHandler;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

class BannerComponentHandlerTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var BannerComponentHandler
     */
    private $bannerComponentHandler;

    public function test_supports_function_should_return_false()
    {
        $this->beforeTest();

        $supports = $this->bannerComponentHandler->supports('my-unknown-component');

        $this->assertFalse($supports);
    }

    public function test_supports_should_succeed()
    {
        $this->beforeTest();

        $supports = $this->bannerComponentHandler->supports('emotion-digital-publishing');

        $this->assertTrue($supports);
    }

    public function test_import_should_return_element_unchanged()
    {
        $this->beforeTest();

        $element = require __DIR__ . '/Fixtures/BannerElement.php';
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);
        unset($element['data']);

        $element = $this->bannerComponentHandler->export($element, $syncData);
        $returnElement = $this->bannerComponentHandler->import($element, $syncData);

        $this->assertJsonStringEqualsJsonString(json_encode($element), json_encode($returnElement));
    }

    public function test_import_should_succeed()
    {
        $this->beforeTest();

        $this->createTestBanners();

        $element = require __DIR__ . '/Fixtures/BannerElement.php';
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);

        $element = $this->bannerComponentHandler->export($element, $syncData);

        $syncData->set('importedAssets', []);
        $assets = $syncData->get('assets');

        foreach ($assets as &$asset) {
            $asset = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';
        }
        unset($asset);

        $syncData->set('assets', $assets);

        $returnElement = $this->bannerComponentHandler->import($element, $syncData);

        $media = $this->container->get('models')->getConnection()->createQueryBuilder()
            ->select('*')
            ->from('s_media')
            ->setMaxResults(2)
            ->orderBy('id', 'DESC')
            ->execute()
            ->fetchAll();

        $this->assertRegExp('/gif-base64-/', $media[0]['name']);
        $this->assertRegExp('/gif-base64-/', $media[1]['name']);
        $this->assertEquals(-3, $media[0]['albumID']);

        $dataValue = json_decode(json_decode($returnElement['data'][1]['value'], true), true);
        $imageLayer = $dataValue['layers'][1];
        $payload = json_decode($imageLayer['elements'][0]['payload'], true);

        $this->assertEquals($media[0]['id'], $payload['mediaId']);
        $this->assertEquals($media[1]['id'], $dataValue['mediaId']);
    }

    public function test_export_should_return_element_unchanged()
    {
        $this->beforeTest();

        $element = require __DIR__ . '/Fixtures/BannerElement.php';
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);
        unset($element['data']);

        $returnElement = $this->bannerComponentHandler->export($element, $syncData);

        $this->assertJsonStringEqualsJsonString(json_encode($element), json_encode($returnElement));
    }

    public function test_export_should_succeed()
    {
        $this->beforeTest();

        $this->createTestBanners();

        $element = require __DIR__ . '/Fixtures/BannerElement.php';
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);
        $returnElement = $this->bannerComponentHandler->export($element, $syncData);

        $dataValue = $returnElement['data'][1]['value'];

        $this->assertCount(2, $syncData->get('assets'));
        $this->assertArrayHasKey($dataValue, $syncData->get('banners'));
    }

    public function test_import_with_translations_should_succeed()
    {
        $this->beforeTest();

        $data = json_decode(require __DIR__ . '/Fixtures/BannerElementWithTranslations.php', true);

        $syncData = new ParameterBag($data['syncData']);

        $this->bannerComponentHandler->import($data['elements'][0], $syncData);

        $query = $this->container->get('models')->getConnection()->createQueryBuilder();

        // check if translation where imported correctly
        $result = $query->select('objecttype, objectdata, objectlanguage, dirty')
            ->from('s_core_translations')
            ->where('objecttype = "contentBannerElement" OR objecttype = "digipubLink"')
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertContains([
            'objecttype' => 'contentBannerElement',
            'objectdata' => 'a:1:{s:4:"text";s:12:"eb1 text1 en";}',
            'objectlanguage' => '2',
            'dirty' => '1',
        ], $result);

        $this->assertContains([
            'objecttype' => 'contentBannerElement',
            'objectdata' => 'a:2:{s:4:"text";s:11:"eb1 but1 de";s:4:"link";s:14:"http://test.en";}',
            'objectlanguage' => '2',
            'dirty' => '1',
        ], $result);

        $this->assertContains([
            'objecttype' => 'contentBannerElement',
            'objectdata' => 'a:1:{s:3:"alt";s:6:"alt en";}',
            'objectlanguage' => '2',
            'dirty' => '1',
        ], $result);

        $this->assertContains([
            'objecttype' => 'digipubLink',
            'objectdata' => 'a:1:{s:4:"link";s:16:"http://ebene.com";}',
            'objectlanguage' => '2',
            'dirty' => '1',
        ], $result);
    }

    public function test_export_with_translations_should_succeed()
    {
        $this->beforeTest();
        $this->createTestBanners();
        $this->createTestBannerTranslation();

        $element = require __DIR__ . '/Fixtures/BannerElement.php';
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);
        $this->bannerComponentHandler->export($element, $syncData);
        $translations = $syncData->get('bannerTranslations');

        $originalTranslations = require __DIR__ . '/Fixtures/BannerTranslationsExportArray.php';
        $this->assertEquals($originalTranslations, $translations);
    }

    private function beforeTest()
    {
        $this->container = Shopware()->Container();

        $mediaServiceMock = $this->createMock(MediaService::class);
        $mediaServiceMock->method('getUrl')->willReturn('data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=');

        $this->bannerComponentHandler = new BannerComponentHandler(
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

    private function createTestBannerTranslation()
    {
        $sql = file_get_contents(__DIR__ . '/../../../Fixtures/testBannersTranslation.sql');
        $this->container->get('models')->getConnection()->exec($sql);
    }
}
