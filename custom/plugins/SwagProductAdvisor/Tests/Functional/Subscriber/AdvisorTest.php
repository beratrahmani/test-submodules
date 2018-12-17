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

namespace SwagProductAdvisor\Tests\Functional\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use SwagProductAdvisor\Subscriber\Advisor;
use SwagProductAdvisor\Subscriber\Resources;
use SwagProductAdvisor\Tests\TestCase;

class AdvisorTest extends TestCase
{
    /**
     * @var Advisor
     */
    private $subscriber;

    public function setUp()
    {
        parent::setUp();

        $this->subscriber = Shopware()->Container()->get('swag_product_advisor.subscriber.advisor');
    }

    public function test_onCollectMediaPositions()
    {
        $result = $this->subscriber->onCollectMediaPositions();

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertTrue(count($result) >= 2);
    }

    public function test_registerMenuIcon()
    {
        $view = new AdvisorTestViewMock();
        $subject = new AdvisorTestSubjectMock($view);
        $arguments = new AdvisorTestArgumentsMock($subject);
        $resourceSubscriber = new Resources(Shopware()->Container()->getParameter('swag_product_advisor.plugin_dir'));

        $resourceSubscriber->registerTemplates($arguments);
        $this->subscriber->registerMenuIcon($arguments);

        $this->assertStringEndsWith('Resources/views/', $view->templateDir);
        $this->assertStringEndsWith('backend/advisor/advisor_menu_item.tpl', $view->extendTemplate);
    }
}

class AdvisorTestArgumentsMock extends \Enlight_Controller_ActionEventArgs
{
    /**
     * @var AdvisorTestSubjectMock
     */
    public $subjectMock;

    /**
     * @param AdvisorTestSubjectMock $subjectMock
     */
    public function __construct(AdvisorTestSubjectMock $subjectMock)
    {
        $this->subjectMock = $subjectMock;
    }

    /**
     * @return AdvisorTestSubjectMock
     */
    public function getSubject()
    {
        return $this->subjectMock;
    }

    /**
     * @param $key
     *
     * @return AdvisorTestSubjectMock
     */
    public function get($key)
    {
        return $this->getSubject();
    }
}

class AdvisorTestSubjectMock
{
    /**
     * @var AdvisorTestViewMock
     */
    public $viewMock;

    /**
     * @param AdvisorTestViewMock $viewMock
     */
    public function __construct(AdvisorTestViewMock $viewMock)
    {
        $this->viewMock = $viewMock;
    }

    /**
     * @return AdvisorTestViewMock
     */
    public function View()
    {
        return $this->viewMock;
    }
}

class AdvisorTestViewMock
{
    /**
     * @var string
     */
    public $templateDir;

    /**
     * @var string
     */
    public $extendTemplate;

    /**
     * @return bool
     */
    public function hasTemplate()
    {
        return true;
    }

    /**
     * @param $value string
     */
    public function addTemplateDir($value)
    {
        $this->templateDir = $value;
    }

    /**
     * @param $value string
     */
    public function extendsTemplate($value)
    {
        $this->extendTemplate = $value;
    }
}
