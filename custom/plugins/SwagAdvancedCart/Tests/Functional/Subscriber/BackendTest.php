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

namespace SwagAdvancedCart\Tests\Functional\Subscriber;

use SwagAdvancedCart\Subscriber\Backend;
use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class BackendTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;

    public function test_onPostDispatchBackend_there_should_be_a_template()
    {
        $viewMock = new BackendSubscriberViewMock();
        $requestMock = new RequestMock('index');
        $subjectMock = new BackendSubscriberSubjectMock($viewMock, $requestMock);
        $enlightEventArgsMock = new BackendSubscriberEventArgsMock($subjectMock);

        $this->getBackend()->onPostDispatchBackend($enlightEventArgsMock);

        $view = $enlightEventArgsMock->getView();

        $this->assertSame('backend/cart_menu_item.tpl', $view->template);
    }

    private function getBackend()
    {
        $path = realpath(__DIR__ . '/../../../');

        return new Backend($path);
    }
}

class BackendSubscriberEventArgsMock extends \Enlight_Event_EventArgs
{
    /**
     * @var BackendSubscriberSubjectMock
     */
    public $subject;

    /**
     * @param BackendSubscriberSubjectMock $subject
     */
    public function __construct(BackendSubscriberSubjectMock $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return BackendSubscriberSubjectMock
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return BackendSubscriberViewMock
     */
    public function getView()
    {
        return $this->subject->View();
    }

    public function get($key)
    {
        return $this->getSubject();
    }
}

class BackendSubscriberSubjectMock
{
    /**
     * @var BackendSubscriberViewMock
     */
    public $view;

    /**
     * @var RequestMock
     */
    public $requestMock;

    /**
     * @param BackendSubscriberViewMock $view
     * @param RequestMock               $requestMock
     */
    public function __construct(BackendSubscriberViewMock $view, RequestMock $requestMock)
    {
        $this->view = $view;
        $this->requestMock = $requestMock;
    }

    /**
     * @return BackendSubscriberViewMock
     */
    public function View()
    {
        return $this->view;
    }

    public function Request()
    {
        return $this->requestMock;
    }
}

class BackendSubscriberViewMock
{
    /**
     * @var string
     */
    public $templateDir;

    /**
     * @var string
     */
    public $template;

    /**
     * @param string $path
     */
    public function addTemplateDir($path)
    {
        $this->templateDir = $path;
    }

    /**
     * @param string $template
     */
    public function extendsTemplate($template)
    {
        $this->template = $template;
    }
}

class RequestMock
{
    /**
     * @var string
     */
    public $controllerName;

    /**
     * @param string $controllerName
     */
    public function __construct($controllerName)
    {
        $this->controllerName = $controllerName;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }
}
