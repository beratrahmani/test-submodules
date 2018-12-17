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

namespace SwagDigitalPublishing\Tests\Functional\Subscriber;

use SwagDigitalPublishing\Components\Emotion\Preset\ComponentHandler\BannerComponentHandler;
use SwagDigitalPublishing\Components\Emotion\Preset\ComponentHandler\BannerSliderComponentHandler;
use SwagDigitalPublishing\Subscriber\ContentBannerSubscriber;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class ContentBannerSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    public function test_register_component_handler()
    {
        $subscriber = new ContentBannerSubscriber(
            Shopware()->Container()->getParameter('swag_digital_publishing.plugin_dir'),
            Shopware()->Container(),
            Shopware()->Container()->get('shopware_media.media_service'),
            Shopware()->Container()->get('models'),
            Shopware()->Container()->get('template')
        );

        $collection = $subscriber->registerComponentHandler();

        $this->assertCount(2, $collection);
        $this->assertInstanceOf(BannerComponentHandler::class, $collection->get(0));
        $this->assertInstanceOf(BannerSliderComponentHandler::class, $collection->get(1));
    }
}
