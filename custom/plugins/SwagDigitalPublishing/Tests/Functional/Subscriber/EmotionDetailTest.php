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

use Enlight_Controller_ActionEventArgs;
use SwagDigitalPublishing\Subscriber\EmotionDetail;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class EmotionDetailTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    public function test_onEmotionDetail_should_extends_template()
    {
        $view = new EmotionDetailTest_ResourcesViewMock();
        $request = new EmotionDetailTest_ResourcesRequestMock('only-extends-template');
        $resource = new EmotionDetailTest_ResourcesSubjectMock($view);
        $arguments = new EmotionDetailTest_ResourcesEventArgsMock($resource, $request);

        $subscriber = $this->getSubscriber();

        $subscriber->onEmotionDetail($arguments);

        $this->assertStringEndsWith('views', $view->templateDir);
        $this->assertStringEndsWith('view/detail/elements/digital_publishing.js', $view->templates[0]);
        $this->assertStringEndsWith('view/detail/elements/digital_publishing_slider.js', $view->templates[1]);
    }

    public function test_onEmotionDetail()
    {
        $view = new EmotionDetailTest_ResourcesViewMock();
        $request = new EmotionDetailTest_ResourcesRequestMock('detail');
        $resource = new EmotionDetailTest_ResourcesSubjectMock($view);
        $arguments = new EmotionDetailTest_ResourcesEventArgsMock($resource, $request);

        $sql = file_get_contents(__DIR__ . '/Fixtures/previewData.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $viewAssign = require __DIR__ . '/Fixtures/viewAssign.php';
        $view->assign('data', $viewAssign);

        $subscriber = $this->getSubscriber();

        $subscriber->onEmotionDetail($arguments);

        $sql = 'SELECT value FROM s_emotion_element_value WHERE id = 49000';
        $result = json_decode(
            json_decode(
                Shopware()->Container()->get('dbal_connection')->fetchColumn($sql), true),
            true
        );

        $expectedResult = 'Blog-Sonnencreme-Beach5037263ca2584.jpg';

        $this->assertStringEndsWith($expectedResult, $result['media']['source']);
        $this->assertStringEndsWith('views', $view->templateDir);
        $this->assertStringEndsWith('view/detail/elements/digital_publishing.js', $view->templates[0]);
        $this->assertStringEndsWith('view/detail/elements/digital_publishing_slider.js', $view->templates[1]);
    }

    public function test_getSubscribedEvents()
    {
        $subscriber = $this->getSubscriber();

        $expectedResult = [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion' => 'onEmotionDetail',
        ];

        $result = $subscriber::getSubscribedEvents();

        $this->assertArraySubset($expectedResult, $result);
    }

    public function test_validateMedia_should_be_false()
    {
        $subscriber = $this->getSubscriber();

        $reflectionClass = new \ReflectionClass(EmotionDetail::class);
        $method = $reflectionClass->getMethod('validateMedia');
        $method->setAccessible(true);

        $result = $method->invoke(
            $subscriber,
            [
                'source' => 'http://this.is.a.sample.url.test',
            ],
            'should.not.be.there'
        );

        $this->assertFalse($result);
    }

    public function test_validateMedia_should_be_true()
    {
        $subscriber = $this->getSubscriber();

        $reflectionClass = new \ReflectionClass(EmotionDetail::class);
        $method = $reflectionClass->getMethod('validateMedia');
        $method->setAccessible(true);

        $result = $method->invoke(
            $subscriber,
            [
                'source' => 'http://should.be.there.sample.url.test',
            ],
            'should.be.there'
        );

        $this->assertTrue($result);
    }

    private function getSubscriber()
    {
        return new EmotionDetail(
            'test/path',
            Shopware()->Container()->get('shopware_storefront.media_service'),
            Shopware()->Container()->get('legacy_struct_converter'),
            Shopware()->Container()->get('models'),
            Shopware()->Container()->get('shopware_storefront.context_service'),
            Shopware()->Container()->get('dbal_connection')
        );
    }
}

class EmotionDetailTest_ResourcesEventArgsMock extends Enlight_Controller_ActionEventArgs
{
    /**
     * @var EmotionDetailTest_ResourcesSubjectMock
     */
    public $subject;

    /**
     * @var EmotionDetailTest_ResourcesRequestMock
     */
    public $request;

    /**
     * @param EmotionDetailTest_ResourcesSubjectMock $subject
     * @param EmotionDetailTest_ResourcesRequestMock $request
     */
    public function __construct($subject, $request)
    {
        $this->subject = $subject;
        $this->request = $request;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return EmotionDetailTest_ResourcesRequestMock
     */
    public function getRequest()
    {
        return $this->request;
    }
}

class EmotionDetailTest_ResourcesSubjectMock
{
    /**
     * @var EmotionDetailTest_ResourcesViewMock
     */
    public $view;

    /**
     * @param EmotionDetailTest_ResourcesViewMock $view
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * @return EmotionDetailTest_ResourcesViewMock
     */
    public function View()
    {
        return $this->view;
    }
}

class EmotionDetailTest_ResourcesViewMock extends \Enlight_View_Default
{
    /**
     * @var string
     */
    public $templateDir;

    /**
     * @var string[]
     */
    public $templates;

    /**
     * @var array
     */
    public $assign;

    public function __construct()
    {
        $this->templates = [];
        $this->assign = [];
    }

    /**
     * @param string $value
     * @param null   $key
     *
     * @return \Enlight_View_Default|void
     */
    public function addTemplateDir($value, $key = null)
    {
        $this->templateDir = $value;
    }

    /**
     * @param $value
     *
     * @return $this|\Enlight_View_Default
     */
    public function extendsTemplate($value)
    {
        $this->templates[] = $value;

        return $this;
    }

    /**
     * @param string $spec
     * @param mixed  $value
     * @param null   $nocache
     * @param null   $scope
     *
     * @return \Enlight_View|\Enlight_View_Default|void
     */
    public function assign($spec, $value = null, $nocache = null, $scope = null)
    {
        $this->assign[$spec] = $value;
    }

    /**
     * @param string $spec
     *
     * @return mixed
     */
    public function getAssign($spec = null)
    {
        return $this->assign[$spec];
    }
}

class EmotionDetailTest_ResourcesRequestMock
{
    /**
     * @var string
     */
    public $actionName;

    /**
     * @param string $actionName
     */
    public function __construct($actionName)
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
