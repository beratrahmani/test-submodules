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

require_once __DIR__ . '/../../../Subscriber/EmotionExtension.php';

use Enlight_Controller_ActionEventArgs;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use SwagEmotionAdvanced\Subscriber\EmotionExtension;
use SwagEmotionAdvanced\tests\KernelTestCaseTrait;

class EmotionExtensionTest extends TestCase
{
    use KernelTestCaseTrait;

    public function test_getSubscribedEvents()
    {
        $result = EmotionExtension::getSubscribedEvents();

        $this->assertTrue(is_array($result));
    }

    public function test_onPostDispatchDetail_no_data()
    {
        $arguments = $this->getArguments();
        $subscriber = new EmotionExtension();

        $subscriber->onPostDispatchDetail($arguments);

        $this->assertNull($arguments->subject->view->loadedTemplates);
    }

    public function test_onPostDispatchDetail()
    {
        $arguments = $this->getArguments('index', ['isEmotionAdvancedQuickView' => true]);
        $subscriber = new EmotionExtension();

        $subscriber->onPostDispatchDetail($arguments);

        $expectedSubset = [
            'widgets/swag_emotion_advanced/index.tpl',
        ];

        $this->assertArraySubset($expectedSubset, $arguments->subject->view->loadedTemplates);
    }

    /**
     * @param string $actionName
     * @param array  $requestParams
     *
     * @return EmotionExtensionTestArgumentsMock
     */
    private function getArguments($actionName = '', array $requestParams = [])
    {
        $view = new EmotionExtensionTestViewMock();
        $request = new EmotionExtensionTestRequestMock($actionName, $requestParams);
        $subject = new EmotionExtensionTestSubjectMock($view);

        return new EmotionExtensionTestArgumentsMock($request, $subject);
    }
}

class EmotionExtensionTestTemplateManagerMock extends Enlight_Template_Manager
{
    /**
     * @var array
     */
    public $loadedTemplateDirs;

    public function __construct()
    {
        $this->loadedTemplateDirs = [];
    }

    /**
     * @param array|string $template_dir
     * @param null         $key
     * @param null         $position
     *
     * @return \Smarty|void
     */
    public function addTemplateDir($template_dir, $key = null, $position = null)
    {
        $this->loadedTemplateDirs[] = $template_dir;
    }
}

class EmotionExtensionTestArgumentsMock extends Enlight_Controller_ActionEventArgs
{
    /**
     * @var EmotionExtensionTestRequestMock
     */
    public $request;

    /**
     * @var EmotionExtensionTestSubjectMock
     */
    public $subject;

    /**
     * @param EmotionExtensionTestRequestMock $request
     * @param EmotionExtensionTestSubjectMock $subject
     */
    public function __construct(EmotionExtensionTestRequestMock $request, EmotionExtensionTestSubjectMock $subject)
    {
        $this->request = $request;
        $this->subject = $subject;
    }

    /**
     * @return EmotionExtensionTestRequestMock
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return EmotionExtensionTestSubjectMock
     */
    public function getSubject()
    {
        return $this->subject;
    }
}

class EmotionExtensionTestSubjectMock
{
    /**
     * @var EmotionExtensionTestViewMock
     */
    public $view;

    /**
     * @param EmotionExtensionTestViewMock $view
     */
    public function __construct(EmotionExtensionTestViewMock $view)
    {
        $this->view = $view;
    }

    /**
     * @return EmotionExtensionTestViewMock
     */
    public function View()
    {
        return $this->view;
    }
}

class EmotionExtensionTestViewMock
{
    /**
     * @var array
     */
    public $loadedTemplates;

    /**
     * @param $templateName
     */
    public function loadTemplate($templateName)
    {
        $this->loadedTemplates[] = $templateName;
    }
}

class EmotionExtensionTestRequestMock
{
    /**
     * @var string
     */
    public $actionName;

    /**
     * @var array
     */
    public $params;

    /**
     * @param $actionName
     * @param array $params
     */
    public function __construct($actionName, array $params = [])
    {
        $this->actionName = $actionName;
        $this->params = $params;
    }

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed|null
     */
    public function getParam($key, $default = null)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }

        return $default;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }
}
