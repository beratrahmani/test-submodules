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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\DependencyInjection\Container;
use SwagDigitalPublishing\Services\ContentBanner;
use SwagDigitalPublishing\Services\PopulateElementHandlerFactory;
use SwagDigitalPublishing\Services\TranslationService;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class ContentBannerTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    /**
     * @var ContentBanner
     */
    private $contentBanner;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var Connection
     */
    private $dbalConnection;

    public function test_get_should_contain_a_simple_banner()
    {
        $this->beforeTest();

        $this->createTestBanners();
        $banner = $this->contentBanner->get(1500991, $this->contextService->createShopContext(1));

        $this->assertEquals('Test Banner 1', $banner['name']);
        $this->assertCount(1, $banner['layers']);
    }

    public function test_get_should_contain_banner_with_image()
    {
        $this->beforeTest();

        $this->createTestBanners();

        $imageSuffix = 'Strandkleid-rot.jpg';

        $banner = $this->contentBanner->get(3500993, $this->contextService->createShopContext(1));

        $this->assertStringEndsWith($imageSuffix, $banner['media']['source']);
        $this->assertCount(3, $banner['media']['thumbnails']);
    }

    public function test_get_should_contain_translations()
    {
        $this->beforeTest();

        $this->createTestBanners();

        /* call addServices with param to create a own shopContext with the english shop */
        $this->addServices();

        $this->dbalConnection->exec(
            file_get_contents(__DIR__ . '/Fixtures/testBannersTranslation.sql')
        );

        $banner = $this->contentBanner->get(3500993, $this->contextService->createShopContext(2));

        $firstSubset = ['text' => 'At vero eos et accusam'];
        $secondSubset = ['link' => 'https://www.google.com/'];

        $firstElement = $banner['layers'][376803]['elements'][0];
        $secondElement = $banner['layers'][376803]['elements'][1];

        $this->assertArraySubset($firstSubset, $firstElement);
        $this->assertArraySubset($secondSubset, $secondElement);
    }

    public function test_populateBanner()
    {
        $this->beforeTest();

        $this->createTestBanners();
        $subset = [
            'id' => 44444,
            'name' => 'Hintergrund',
            'bgType' => 'image',
            'bgOrientation' => 'center center',
            'bgMode' => 'cover',
            'bgColor' => '#001DF7',
            'mediaId' => 781,
        ];

        $bannerArray = require __DIR__ . '/Fixtures/BannerArray.php';
        $result = $this->contentBanner->populateBanner($bannerArray, $this->contextService->createShopContext(1));

        $this->assertArraySubset($subset, $result);
        $this->assertCount(2, $result['layers']);
    }

    public function test_populateBanner_with_deleted_images_should_be_populated()
    {
        $this->beforeTest();

        $bannerArray = require __DIR__ . '/Fixtures/BannerArrayDeletedImages.php';
        $result = $this->contentBanner->populateBanner($bannerArray, $this->contextService->createShopContext(1));

        $this->assertFalse(array_key_exists('media', $result));
    }

    public function testPopulateBanner_with_product_link()
    {
        $this->beforeTest();

        $sql = file_get_contents(__DIR__ . '/Fixtures/bannerLink.sql');
        $this->dbalConnection->exec($sql);
        $bannerArray = require __DIR__ . '/Fixtures/BannerArrayProductLink.php';
        $result = $this->contentBanner->populateBanner($bannerArray, $this->contextService->createShopContext(1));

        $this->assertArrayHasKey('product', $result['layers'][0]);
        $this->assertSame(178, $result['layers'][0]['product']['articleID']);
    }

    private function beforeTest()
    {
        $this->container = Shopware()->Container();
        $this->dbalConnection = $this->container->get('dbal_connection');
        $this->contextService = $this->container->get('shopware_storefront.context_service');

        $this->addServices();
    }

    private function createTestBanners()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/testBanners.sql');
        $this->dbalConnection->exec($sql);
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

        $this->contentBanner = new ContentBanner(
            Shopware()->Container()->get('models'),
            Shopware()->Container()->get('digital_publishing.translation_service'),
            Shopware()->Container()->get('shopware_storefront.list_product_service'),
            Shopware()->Container()->get('shopware_storefront.media_service'),
            Shopware()->Container()->get('digital_publishing.populate_element_handler_factory'),
            Shopware()->Container()->get('events'),
            Shopware()->Container()->get('legacy_struct_converter')
        );
    }
}
