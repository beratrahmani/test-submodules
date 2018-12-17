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

use SwagProductAdvisor\Components\Helper\RewriteUrlGenerator;
use SwagProductAdvisor\Subscriber\Resources;
use SwagProductAdvisor\Subscriber\Seo;
use SwagProductAdvisor\Tests\TestCase;

class SeoTest extends TestCase
{
    /**
     * @var Seo
     */
    private $subscriber;

    public function setUp()
    {
        parent::setUp();

        $this->subscriber = Shopware()->Container()->get('swag_product_advisor.subscriber.seo');
    }

    public function test_onLoadBackendModule_view_vars_should_be_empty()
    {
        $view = new SeoTestViewMock();
        $request = new SeoTestRequestMock();
        $subject = new SeoTestSubjectMock($view, $request);
        $argument = new SeoTestArgumentsMock($subject);

        $request->setActionName('notRegisteredAction');

        $this->subscriber->onLoadBackendModule($argument);

        $this->assertEmpty($view->templateDir);
        $this->assertEmpty($view->extendTemplate);
    }

    public function test_onLoadBackendModule_view_vars_should_be_filled()
    {
        $view = new SeoTestViewMock();
        $request = new SeoTestRequestMock();
        $subject = new SeoTestSubjectMock($view, $request);
        $argument = new SeoTestArgumentsMock($subject);
        $resourceSubscriber = new Resources(Shopware()->Container()->getParameter('swag_product_advisor.plugin_dir'));

        $request->setActionName('load');

        $resourceSubscriber->registerTemplates($argument);
        $this->subscriber->onLoadBackendModule($argument);

        $this->assertNotEmpty($view->templateDir);
        $this->assertNotEmpty($view->extendTemplate);

        $this->assertStringEndsWith('/Resources/views/', $view->templateDir[0]);
        $this->assertStringEndsWith('/view/advisor.js', $view->extendTemplate[0]);
    }

    public function test_addSeoCount_should_add_count()
    {
        $eventArgs = new \Enlight_Event_EventArgs();
        $eventArgs->setReturn(
            [
                'test' => 15,
            ]
        );

        $seoSubscriber = new Seo(
            new RewriteUrlGeneratorMock(
                Shopware()->Container()->get('models'),
                Shopware()->Container()->get('dbal_connection'),
                Shopware()->Container()->get('swag_product_advisor.dependency_provider')
            )
        );

        $newCounts = $seoSubscriber->addSeoCount($eventArgs);

        self::assertEquals(['test' => 15, 'advisor' => 2], $newCounts);
    }

    public function test_onFilterRewriteQuery_should_be_empty()
    {
        $view = new SeoTestViewMock();
        $request = new SeoTestRequestMock();
        $subject = new SeoTestSubjectMock($view, $request);
        $argument = new SeoTestArgumentsMock($subject);

        $result = $this->subscriber->onFilterRewriteQuery($argument);

        $this->assertEmpty($result);
    }

    public function test_onFilterRewriteQuery_there_should_be_a_advisor_id()
    {
        $view = new SeoTestViewMock();
        $request = new SeoTestRequestMock();
        $subject = new SeoTestSubjectMock($view, $request);
        $argument = new SeoTestArgumentsMock($subject);

        $argument->setQuery(['controller' => 'advisor', 'advisorId' => '112']);
        $argument->setReturn(['sAction' => 'set']);

        $result = $this->subscriber->onFilterRewriteQuery($argument);

        $this->assertSame('112', $result['advisorId']);
    }

    public function test_onCreateRewriteTable()
    {
        $view = new SeoTestViewMock();
        $request = new SeoTestRequestMock();
        $subject = new SeoTestSubjectMock($view, $request);
        $argument = new SeoTestArgumentsMock($subject);

        $expectedResult = ['sAction' => 'set'];
        $argument->setReturn($expectedResult);

        $result = $this->subscriber->onCreateRewriteTable($argument);

        $this->assertEquals($expectedResult, $result);
    }
}

class SeoTestRewriteUrlGeneratorServiceMock extends RewriteUrlGenerator
{
    public function countAdvisorUrls()
    {
        return 112;
    }
}

class SeoTestArgumentsMock extends \Enlight_Controller_ActionEventArgs
{
    /**
     * @var SeoTestSubjectMock
     */
    public $subjectMock;

    /**
     * @var mixed
     */
    public $returnData;

    /**
     * @var mixed
     */
    public $queryData;

    /**
     * @param SeoTestSubjectMock $subjectMock
     */
    public function __construct(SeoTestSubjectMock $subjectMock)
    {
        $this->subjectMock = $subjectMock;
    }

    public function getQuery()
    {
        return $this->queryData;
    }

    /**
     * @param $query mixed
     */
    public function setQuery($query)
    {
        $this->queryData = $query;
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->returnData;
    }

    /**
     * @param mixed $data
     */
    public function setReturn($data)
    {
        $this->returnData = $data;
    }

    /**
     * @return SeoTestSubjectMock
     */
    public function getSubject()
    {
        return $this->subjectMock;
    }

    /**
     * @param $key
     *
     * @return SeoTestSubjectMock
     */
    public function get($key)
    {
        return $this->getSubject();
    }
}

class SeoTestSubjectMock
{
    /**
     * @var SeoTestViewMock
     */
    public $viewMock;

    /**
     * @var SeoTestRequestMock
     */
    public $requestMock;

    /**
     * @param SeoTestViewMock    $viewMock
     * @param SeoTestRequestMock $requestMock
     */
    public function __construct(SeoTestViewMock $viewMock, SeoTestRequestMock $requestMock)
    {
        $this->viewMock = $viewMock;
        $this->requestMock = $requestMock;
    }

    public function Request()
    {
        return $this->requestMock;
    }

    /**
     * @return SeoTestViewMock
     */
    public function View()
    {
        return $this->viewMock;
    }
}

class SeoTestViewMock
{
    /**
     * @var array
     */
    public $templateDir;

    /**
     * @var array
     */
    public $viewAssign;

    /**
     * @var array
     */
    public $extendTemplate;

    public function __construct()
    {
        $this->templateDir = [];
        $this->extendTemplate = [];
        $this->viewAssign = [];
    }

    /**
     * @param $key string
     * @param $data array
     */
    public function assign($key, $data)
    {
        $this->viewAssign[$key] = $data;
    }

    /**
     * @param $key string
     *
     * @return mixed
     */
    public function getAssign($key)
    {
        return $this->viewAssign[$key];
    }

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
        $this->templateDir[] = $value;
    }

    /**
     * @param $value string
     */
    public function extendsTemplate($value)
    {
        $this->extendTemplate[] = $value;
    }
}

class SeoTestRequestMock
{
    /**
     * @var string
     */
    public $actionName;

    /**
     * @param $actionName string
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }
}

class RewriteUrlGeneratorMock extends RewriteUrlGenerator
{
    public function countAdvisorUrls()
    {
        return 2;
    }
}
