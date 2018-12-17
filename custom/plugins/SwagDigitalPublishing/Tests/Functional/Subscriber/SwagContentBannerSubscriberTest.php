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

namespace ShopwarePlugins\SwagDigitalPublishing\tests\Functional\Subscriber;

use SwagDigitalPublishing\Subscriber\SwagContentBannerSubscriber;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class SwagContentBannerSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    public function test_onWidgetsPostDispatch()
    {
        $this->createBanner();
        $templateManager = Shopware()->Container()->get('template');

        $subscriber = new SwagContentBannerSubscriber(
            dirname(dirname(dirname(__DIR__))) . '/',
            $templateManager
        );

        $subscriber->onWidgetsPostDispatch();

        /** @var array $result */
        $result = $templateManager->getTemplateDir();

        $resultSuffix = 'Resources/views/';

        $isTemplateThere = false;
        foreach ($result as $templateDir) {
            if (strpos($templateDir, $resultSuffix)) {
                $isTemplateThere = true;
                break;
            }
        }

        $this->assertTrue($isTemplateThere);
    }

    private function createBanner()
    {
        $sql = file_get_contents(__DIR__ . '/Fixtures/banner.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
    }
}

class SwagContentBannerSubscriberArgumentsMock extends \Enlight_Event_EventArgs
{
    /**
     * @var array
     */
    public $data;

    /**
     * @var mixed
     */
    public $returnDataMock;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setToData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key];
    }

    /**
     * @param mixed $data
     */
    public function setReturn($data)
    {
        $this->returnDataMock = $data;
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->returnDataMock;
    }
}
