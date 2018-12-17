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

namespace SwagProductAdvisor;

use SwagProductAdvisor\Bundle\AdvisorBundle\Struct\Advisor as AdvisorStruct;

require_once __DIR__ . '/../../../../Controllers/Frontend/Advisor.php';

class AdvisorTest extends \Enlight_Components_Test_Controller_TestCase
{
    public function test_getViewConfig_should_not_convert_result_to_array()
    {
        $advisorCtl = $this->getAdvisor();
        $advisorCtl->setContainer(Shopware()->Container());
        $viewConfig = $advisorCtl->getViewConfig(new AdvisorStructMock(), 'foo');

        self::assertInstanceOf(\stdClass::class, $viewConfig['advisor']['result']);
    }

    public function test_getViewConfig_should_not_convert_tophit_to_array()
    {
        $advisorCtl = $this->getAdvisor();
        $advisorCtl->setContainer(Shopware()->Container());
        $viewConfig = $advisorCtl->getViewConfig(new AdvisorStructMock(), 'foo');

        self::assertInstanceOf(\stdClass::class, $viewConfig['advisor']['topHit']);
    }

    public function test_rewriteProductUrls_should_rewrite_linkDetails()
    {
        $advisorCtl = $this->getAdvisor();
        $advisorCtl->setContainer(Shopware()->Container());
        $reflectionClass = new \ReflectionClass(get_class($advisorCtl));
        $method = $reflectionClass->getMethod('rewriteProductUrls');
        $method->setAccessible(true);

        $result = $method->invokeArgs($advisorCtl, [[
            [
                'linkDetails' => 'shopware.php?sViewport=detail&sArticle=67',
            ],
            [
                'linkDetails' => 'shopware.php?sViewport=detail&sArticle=148',
            ],
        ]]);

        self::assertStringEndsWith('detail/index/sArticle/67', $result[0]['linkDetails']);
        self::assertStringEndsWith('detail/index/sArticle/148', $result[1]['linkDetails']);
    }

    /**
     * @return \Shopware_Controllers_Frontend_Advisor
     */
    private function getAdvisor()
    {
        return \Enlight_Class::Instance(
            'Shopware_Controllers_Frontend_Advisor',
            [
                $this->Request(),
                $this->Response(),
            ]
        );
    }
}

class AdvisorStructMock extends AdvisorStruct
{
    public function __construct()
    {
    }

    public function getResult()
    {
        return (object) ['foo' => 'bar'];
    }

    public function getTopHit()
    {
        return (object) ['bar' => 'baz'];
    }
}
