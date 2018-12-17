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

namespace  SwagPromotion\Tests\Functional\Subscriber;

use Shopware\Components\DependencyInjection\Container;
use SwagPromotion\Subscriber\Backend;
use SwagPromotion\Tests\DatabaseTestCaseTrait;
use SwagPromotion\Tests\PromotionTestCase;

class BackendTest extends PromotionTestCase
{
    use DatabaseTestCaseTrait;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        parent::setUp();

        $this->container = Shopware()->Container();
    }

    public function test_onPostDispatchAnalytics_index_action()
    {
        $this->Request()->setActionName('index');
        $view = new BackendTestViewMock();
        $subject = new BackendTestSubjectMock($view, $this->Request());
        $arguments = new BackendTestArgumentsMock($subject);

        $subscriber = new Backend('this/is/a/test/path');
        $subscriber->onPostDispatchAnalytics($arguments);

        $expectedTemplateDirSuffix = '/Resources/views/';
        $expectedTemplatesSubset = [
            'backend/analytics/promotion_analytics.js',
        ];

        $this->assertArraySubset($expectedTemplatesSubset, $view->templates);
    }

    public function test_onPostDispatchAnalytics_load_action()
    {
        $this->Request()->setActionName('load');
        $view = new BackendTestViewMock();
        $subject = new BackendTestSubjectMock($view, $this->Request());
        $arguments = new BackendTestArgumentsMock($subject);

        $subscriber = new Backend('this/is/a/test/path');
        $subscriber->onPostDispatchAnalytics($arguments);

        $expectedTemplateDirSuffix = '/Resources/views/';
        $expectedTemplatesSubset = [
            'backend/analytics/store/promotion/navigation.js',
            'backend/analytics/store/promotion/details/navigation_details.js',
        ];

        $this->assertArraySubset($expectedTemplatesSubset, $view->templates);
    }

    public function test_onBackendIndex_index_action()
    {
        $this->Request()->setActionName('index');
        $view = new BackendTestViewMock();
        $subject = new BackendTestSubjectMock($view, $this->Request());
        $arguments = new BackendTestArgumentsMock($subject);

        $subscriber = new Backend('this/is/a/test/path');
        $subscriber->onBackendIndex($arguments);

        $expectedTemplatesSubset = [
            'backend/swag_rule_tree/app.js',
            'backend/icons.tpl',
        ];

        $this->assertArraySubset($expectedTemplatesSubset, $view->templates);
        $this->assertCount(1, $view->assignedData);
    }
}

class BackendTestArgumentsMock extends \Enlight_Event_EventArgs
{
    /**
     * @var BackendTestSubjectMock
     */
    public $subject;

    /**
     * @param BackendTestSubjectMock $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return BackendTestSubjectMock
     */
    public function getSubject()
    {
        return $this->subject;
    }
}

class BackendTestSubjectMock
{
    /**
     * @var \Enlight_Controller_Request_RequestTestCase
     */
    public $request;

    /**
     * @var BackendTestViewMock
     */
    public $view;

    /**
     * @param BackendTestViewMock                         $view
     * @param \Enlight_Controller_Request_RequestTestCase $request
     */
    public function __construct($view, $request)
    {
        $this->view = $view;
        $this->request = $request;
    }

    /**
     * @return \Enlight_Controller_Request_RequestTestCase|\Enlight_Controller_Request_RequestTestCase
     */
    public function Request()
    {
        return $this->request;
    }

    /**
     * @return BackendTestViewMock
     */
    public function View()
    {
        return $this->view;
    }
}

class BackendTestViewMock
{
    /**
     * @var string
     */
    public $templateDir;

    /**
     * @var array
     */
    public $assignedData;

    /**
     * @var string[]
     */
    public $templates;

    public function __construct()
    {
        $this->assignedData = [];
        $this->templates = [];
    }

    /**
     * @param $string
     */
    public function addTemplateDir($string)
    {
        $this->templateDir = $string;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function assign($key, $value)
    {
        $this->assignedData[$key] = $value;
    }

    /**
     * @param $string
     */
    public function extendsTemplate($string)
    {
        $this->templates[] = $string;
    }
}
