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

namespace SwagEmotionAdvanced\tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagEmotionAdvanced\Subscriber\Theme;

class ThemeTest extends TestCase
{
    public function test_getSubscribedEvents()
    {
        $events = Theme::getSubscribedEvents();

        $this->assertCount(1, $events);
        $this->assertSame('onTemplateDirectoriesCollect', $events['Theme_Inheritance_Template_Directories_Collected']);
    }

    public function test_onTemplateDirectoriesCollect()
    {
        $subscriber = new Theme('test/foo');

        $args = new \Enlight_Event_EventArgs();

        $subscriber->onTemplateDirectoriesCollect($args);

        $result = $args->getReturn();

        $this->assertSame('test/foo/Resources/views/', $result[0]);
    }
}
