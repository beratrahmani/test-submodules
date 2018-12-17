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
use SwagDigitalPublishing\Subscriber\Resources;
use SwagDigitalPublishing\Tests\KernelTestCaseTrait;

class ResourcesTest extends \PHPUnit_Framework_TestCase
{
    use KernelTestCaseTrait;

    public function test_onPostDispatchFrontend()
    {
        $view = new ResourcesViewMock();
        $resource = new ResourcesSubjectMock($view);
        $arguments = new ResourcesEventArgsMock($resource);

        $subscriber = new Resources('');

        $subscriber->onPostDispatchFrontend($arguments);

        $this->assertStringEndsWith('Resources/views/', $view->templateDir);
    }
}

class ResourcesEventArgsMock extends Enlight_Controller_ActionEventArgs
{
    /**
     * @var ResourcesSubjectMock
     */
    public $subject;

    /**
     * @param ResourcesSubjectMock $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return ResourcesSubjectMock
     */
    public function getSubject()
    {
        return $this->subject;
    }
}

class ResourcesSubjectMock
{
    /**
     * @var ResourcesViewMock
     */
    public $view;

    /**
     * @param ResourcesViewMock $view
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * @return ResourcesViewMock
     */
    public function View()
    {
        return $this->view;
    }
}

class ResourcesViewMock
{
    /**
     * @var string
     */
    public $templateDir;

    /**
     * @var string[]
     */
    public $templates;

    public function __construct()
    {
        $this->templates = [];
    }

    /**
     * @param string $value
     */
    public function addTemplateDir($value)
    {
        $this->templateDir = $value;
    }

    /**
     * @param $value
     */
    public function extendsTemplate($value)
    {
        $this->templates[] = $value;
    }
}
