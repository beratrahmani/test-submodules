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

use SwagAdvancedCart\Subscriber\Detail;
use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class DetailTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;

    public function test_onDetailController()
    {
        Shopware()->Session()->sUserId = 1;
        Shopware()->Session()->offsetSet('sUserId', 1);
        $viewMock = new DetailSubscriberViewMock(new \Enlight_Template_Manager());
        $viewMock->assign('sArticle', require(__DIR__ . '/Fixtures/sArticleViewAssign.php'));

        $subjectMock = new DetailSubscriberSubjectMock($viewMock);
        $enlightEventArgsMock = new DetailSubscriberEventArgsMock($subjectMock);

        $this->getDetail()->onDetailController($enlightEventArgsMock);

        $userCarts = $viewMock->getAssign('allCartsByUser');
        $userId = $viewMock->getAssign('userId');
        $wishLists = $viewMock->getAssign('wishlistArticles');
        $perPage = $viewMock->getAssign('perPage');

        $this->assertEquals([], $userCarts);
        $this->assertEquals(1, $userId);
        $this->assertEquals([], $wishLists);
        $this->assertEquals(4, $perPage);
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function getDetail()
    {
        return new Detail(
            $this->getContainer()->getParameter('swag_advanced_cart.plugin_name'),
            $this->getContainer()->get('swag_advanced_cart.user'),
            $this->getContainer()->get('models'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('shopware.plugin.config_reader'),
            $this->getContainer()->get('swag_advanced_cart.also_list_service')
        );
    }
}

class DetailSubscriberEventArgsMock extends \Enlight_Event_EventArgs
{
    /**
     * @var DetailSubscriberSubjectMock
     */
    public $subject;

    public function __construct(DetailSubscriberSubjectMock $subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getView()
    {
        return $this->subject->View();
    }

    public function get($key)
    {
        return $this->subject;
    }
}

class DetailSubscriberSubjectMock
{
    /**
     * @var DetailSubscriberViewMock
     */
    public $view;

    public function __construct(DetailSubscriberViewMock $view)
    {
        $this->view = $view;
    }

    public function View()
    {
        return $this->view;
    }
}

class DetailSubscriberViewMock extends \Enlight_View_Default
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
     * @var array
     */
    public $assignedData;

    /**
     * @param string $path
     *
     * @return \Enlight_View_Default|void
     */
    public function addTemplateDir($path)
    {
        $this->templateDir = $path;
    }

    /**
     * @param string $template
     *
     * @return \Enlight_View_Default|void
     */
    public function extendsTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @param string $spec
     * @param mixed  $value
     * @param bool   $nocache
     * @param null   $scope
     *
     * @return \Enlight_View|\Enlight_View_Default|void
     */
    public function assign($spec, $value = null, $nocache = false, $scope = null)
    {
        $this->assignedData[$spec] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAssign($key = null)
    {
        if (!$key) {
            return $this->assignedData;
        }

        return $this->assignedData[$key];
    }
}
