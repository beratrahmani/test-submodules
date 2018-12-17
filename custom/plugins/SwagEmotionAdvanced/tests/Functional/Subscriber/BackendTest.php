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

require_once __DIR__ . '/../../../Subscriber/Backend.php';

use Enlight_Controller_ActionEventArgs;
use PHPUnit\Framework\TestCase;
use Shopware_Controllers_Backend_Emotion;
use SwagEmotionAdvanced\Subscriber\Backend;
use SwagEmotionAdvanced\tests\KernelTestCaseTrait;

class BackendTest extends TestCase
{
    use KernelTestCaseTrait;

    public function test_getSubscribedEvents()
    {
        $result = Backend::getSubscribedEvents();

        $this->assertTrue(is_array($result));
    }

    public function test_onBackendPostDispatch_index_action()
    {
        $arguments = $this->getArguments('index');
        $subscriber = $this->getBackendSubscriber();

        $subscriber->onBackendPostDispatch($arguments);

        $expected = ['backend/swag_emotion_advanced/app.js'];
        $this->assertArraySubset($expected, $arguments->subject->view->extendedTemplates);
    }

    public function test_onBackendPostDispatch_load_action()
    {
        $arguments = $this->getArguments('load');
        $subscriber = $this->getBackendSubscriber();

        $subscriber->onBackendPostDispatch($arguments);

        $expected = [
            'backend/swag_emotion_advanced/model/emotion.js',
            'backend/emotion/swag_emotion_advanced/view/components/banner_mapping.js',
        ];

        $this->assertArraySubset($expected, $arguments->subject->view->extendedTemplates);
    }

    public function test_onBackendPostDispatch_detail_action_empty_data()
    {
        $arguments = $this->getArguments('detail');
        $subscriber = $this->getBackendSubscriber();

        $arguments->subject->view->assign('data', []);

        $subscriber->onBackendPostDispatch($arguments);

        $result = $arguments->subject->view->getAssign('data');

        $expected = [
            'swagRows' => 6,
            'swagQuickview' => false,
        ];

        $this->assertArraySubset($expected, $result);
    }

    public function test_onBackendPostDispatch_detail_action_filed_data()
    {
        $arguments = $this->getArguments('detail');
        $subscriber = $this->getBackendSubscriber();

        $data = [
            'attribute' => [
                'swagRows' => 99,
                'swagQuickview' => true,
            ],
        ];

        $arguments->subject->view->assign('data', $data);

        $subscriber->onBackendPostDispatch($arguments);

        $result = $arguments->subject->view->getAssign('data');

        $expected = [
            'swagRows' => 99,
            'swagQuickview' => true,
        ];

        $this->assertArraySubset($expected, $result);
    }

    public function test_onBackendPostDispatch_save_action()
    {
        $data = [
            'id' => 12,
        ];

        $requestData = [
            'swagQuickview' => true,
            'swagRows' => '99',
        ];

        $arguments = $this->getArguments('save', $requestData);
        $arguments->subject->view->assign('data', $data);

        $subscriber = $this->getBackendSubscriber();
        $subscriber->onBackendPostDispatch($arguments);

        $result = $arguments->subject->view->getAssign('data');

        $sql = 'SELECT * FROM s_emotion_attributes';
        $connection = Shopware()->Container()->get('dbal_connection');

        $databaseResult = $connection->fetchAll($sql);
        $databaseResult = array_shift($databaseResult);

        $expectedSubset = [
            'emotionID' => '12',
            'swag_quickview' => '1',
            'swag_rows' => '99',
        ];

        $this->assertArraySubset($data, $result);
        $this->assertArraySubset($expectedSubset, $databaseResult);
    }

    public function test_onBackendPostDispatch_savePreview_action()
    {
        $data = [
            'id' => 12,
        ];

        $requestData = [
            'swagQuickview' => true,
            'swagRows' => '88',
        ];

        $arguments = $this->getArguments('savePreview', $requestData);
        $arguments->subject->view->assign('data', $data);

        $subscriber = $this->getBackendSubscriber();
        $subscriber->onBackendPostDispatch($arguments);

        $result = $arguments->subject->view->getAssign('data');

        $sql = 'SELECT * FROM s_emotion_attributes';
        $connection = Shopware()->Container()->get('dbal_connection');

        $databaseResult = $connection->fetchAll($sql);
        $databaseResult = array_shift($databaseResult);

        $expectedSubset = [
            'emotionID' => '12',
            'swag_quickview' => '1',
            'swag_rows' => '88',
        ];

        $this->assertArraySubset($data, $result);
        $this->assertArraySubset($expectedSubset, $databaseResult);
    }

    /**
     * @param string $actionName
     * @param array  $requestData
     *
     * @return BackendTestEventArgsMock
     */
    private function getArguments($actionName, array $requestData = [])
    {
        $view = new BackendTestViewMock();
        $request = new BackendTestRequestMock($actionName, $requestData);
        $controller = new BackendTestControllerMock($view, $request);

        return new BackendTestEventArgsMock($controller, $request);
    }

    /**
     * @return Backend
     */
    private function getBackendSubscriber()
    {
        $subscriber = new Backend(
            '',
            Shopware()->Container()->get('shopware_attribute.data_persister'),
            Shopware()->Container()->get('shopware_media.media_service')
        );

        return $subscriber;
    }
}

class BackendTestEventArgsMock extends Enlight_Controller_ActionEventArgs
{
    /**
     * @var BackendTestControllerMock
     */
    public $subject;

    /**
     * @var BackendTestRequestMock
     */
    public $request;

    /**
     * @param BackendTestControllerMock $subject
     * @param BackendTestRequestMock    $request
     */
    public function __construct(BackendTestControllerMock $subject, BackendTestRequestMock $request)
    {
        $this->subject = $subject;
        $this->request = $request;
    }

    /**
     * @return BackendTestControllerMock
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return BackendTestRequestMock
     */
    public function getRequest()
    {
        return $this->request;
    }
}

class BackendTestRequestMock
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
     * @param string $actionName
     * @param array  $params
     */
    public function __construct($actionName, array $params = [])
    {
        $this->actionName = $actionName;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
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
}

class BackendTestControllerMock extends Shopware_Controllers_Backend_Emotion
{
    /**
     * @var BackendTestViewMock
     */
    public $view;

    /**
     * @var BackendTestRequestMock
     */
    public $request;

    /**
     * @param BackendTestViewMock    $view
     * @param BackendTestRequestMock $request
     */
    public function __construct(BackendTestViewMock $view, BackendTestRequestMock $request)
    {
        $this->view = $view;
        $this->request = $request;
    }
}

class BackendTestViewMock
{
    /**
     * @var array
     */
    public $templateDirs;

    /**
     * @var array
     */
    public $extendedTemplates;

    /**
     * @var array;
     */
    public $assign;

    public function __construct()
    {
        $this->templateDirs = [];
        $this->extendedTemplates = [];
        $this->assign = [];
    }

    /**
     * @param $string
     */
    public function addTemplateDir($string)
    {
        $this->templateDirs[] = $string;
    }

    /**
     * @param $string
     */
    public function extendsTemplate($string)
    {
        $this->extendedTemplates[] = $string;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function assign($key, $value)
    {
        $this->assign[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAssign($key)
    {
        return $this->assign[$key];
    }
}
