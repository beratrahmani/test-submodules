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

namespace SwagEmotionAdvanced\tests\Functional\Components\Emotion\Preset\ComponentHandler;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Media\Media;
use SwagEmotionAdvanced\Components\Emotion\Preset\ComponentHandler\SideViewComponentHandler;
use SwagEmotionAdvanced\tests\KernelTestCaseTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

class SideViewasdfComponentHandlerTest extends TestCase
{
    use KernelTestCaseTrait;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var SideViewComponentHandler
     */
    private $handler;

    /**
     * @var Media
     */
    private $media;

    /**
     * @var MediaService
     */
    private $mediaService;

    public function setUp()
    {
        parent::setUp();

        $this->container = self::getContainer();

        /** @var \Shopware\Models\Media\Repository $repo */
        $repo = $this->container->get('models')->getRepository(Media::class);

        $this->media = $repo->createQueryBuilder('m')
            ->setMaxResults(1)
            ->orderBy('m.id')
            ->getQuery()
            ->getSingleResult();

        $this->mediaService = $this->container->get('shopware_media.media_service');

        $this->handler = new SideViewComponentHandler(
            $this->container->get('shopware.api.media'),
            $this->mediaService,
            $this->container
        );
    }

    public function test_supports_function_should_return_false()
    {
        $supports = $this->handler->supports('my-unknown-component');

        $this->assertFalse($supports);
    }

    public function test_supports_should_succeed()
    {
        $supports = $this->handler->supports('emotion-sideview-widget');

        $this->assertTrue($supports);
    }

    public function test_import_should_return_element_unchanged()
    {
        $element = require __DIR__ . '/Fixtures/SideViewElement.php';
        $syncData = new ParameterBag();

        $element = $this->handler->export($element, $syncData);
        $parameterBag = new ParameterBag(['assets' => []]);
        unset($element['data']);
        $returnElement = $this->handler->import($element, $parameterBag);

        $this->assertJsonStringEqualsJsonString(json_encode($element), json_encode($returnElement));
    }

    public function test_export_should_return_element_unchanged()
    {
        $element = require __DIR__ . '/Fixtures/SideViewElement.php';
        unset($element['data']);
        $syncData = new ParameterBag();

        $returnElement = $this->handler->export($element, $syncData);

        $this->assertJsonStringEqualsJsonString(json_encode($element), json_encode($returnElement));
    }

    public function test_export_should_succeed()
    {
        if (!$this->media) {
            $this->markTestSkipped('No media for testing in database.');
        }

        $md5Hash = md5($this->media->getId());
        $element = require __DIR__ . '/Fixtures/SideViewElement.php';
        // get test url from media
        $element['data'][0]['value'] = $this->mediaService->getUrl($this->media->getPath());

        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);

        $returnElement = $this->handler->export($element, $syncData);

        $dataValue = $returnElement['data'][0]['value'];

        $this->assertEquals($md5Hash, $dataValue);
    }

    public function test_import_should_succeed()
    {
        if (!$this->media) {
            $this->markTestSkipped('No media for testing in database.');
        }

        $element = require __DIR__ . '/Fixtures/SideViewElement.php';

        // get test url from media
        $element['data'][0]['value'] = $this->mediaService->getUrl($this->media->getPath());
        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);

        $element = $this->handler->export($element, $syncData);

        $syncData->set('importedAssets', []);
        $assets = $syncData->get('assets');

        $assets[key($assets)] = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';
        $syncData->set('assets', $assets);

        $this->handler->import($element, $syncData);

        $media = $this->container->get('models')->getConnection()->createQueryBuilder()
            ->select('*')
            ->from('s_media')
            ->setMaxResults(1)
            ->orderBy('id', 'DESC')
            ->execute()
            ->fetchAll();

        $this->assertRegExp('/gif-base64/', $media[0]['name']);
        $this->assertEquals(-3, $media[0]['albumID']);
    }
}
