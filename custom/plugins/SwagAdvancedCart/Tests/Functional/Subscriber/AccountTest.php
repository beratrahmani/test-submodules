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

use Enlight_Components_Session_Namespace;
use SwagAdvancedCart\Subscriber\Account;
use SwagAdvancedCart\Tests\CustomerLoginTrait;
use SwagAdvancedCart\Tests\KernelTestCaseTrait;

class AccountTest extends \PHPUnit\Framework\TestCase
{
    use KernelTestCaseTrait;
    use CustomerLoginTrait;

    public function test_onBeforeLogin_session_should_contains_old_session_id()
    {
        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->getContainer()->get('session');
        $session->offsetSet('sessionId', 'oldSessionId');

        $subscriber = $subscriber = $this->getSubscriber();

        $subscriber->onBeforeLogin();

        $this->assertEquals('oldSessionId', $session->offsetGet('oldSessionId'));
    }

    public function test_onLogOutSuccessful()
    {
        $this->loginCustomer();
        $view = new AccountSubscriberViewMock();
        $subject = new AccountSubscriberSubjectMock($view);
        $eventArgs = new AccountSubscriberEventArgsMock($subject);

        $this->getConnection()->exec(file_get_contents(__DIR__ . '/Fixtures/createOriginalBasket.sql'));

        $subscriber = $subscriber = $this->getSubscriber();

        $subscriber->onLogOutSuccessful($eventArgs);

        $result = $this->getConnection()->fetchAll(
            "SELECT * FROM s_order_basket WHERE sessionID = 'cookieValue'"
        );

        $this->assertEmpty($result);
    }

    private function getContainer()
    {
        return self::getKernel()->getContainer();
    }

    private function getConnection()
    {
        return $this->getContainer()->get('dbal_connection');
    }

    private function getSubscriber()
    {
        return new Account(
            $this->getContainer()->get('session'),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('swag_advanced_cart.basket_utils'),
            $this->getContainer()->get('swag_advanced_cart.dependency_provider')
        );
    }

    private function readAllSavedBaskets()
    {
        return $this->getConnection()->createQueryBuilder()
            ->select('*')
            ->from('s_order_basket_saved')
            ->execute()
            ->fetchAll();
    }
}

class AccountSubscriberEventArgsMock extends \Enlight_Event_EventArgs
{
    /**
     * @var AccountSubscriberSubjectMock
     */
    public $subject;

    /**
     * @var array
     */
    public $user;

    /**
     * @param AccountSubscriberSubjectMock $subject
     */
    public function __construct(AccountSubscriberSubjectMock $subject)
    {
        $this->subject = $subject;
        $this->user = [
            'id' => 1,
        ];
    }

    /**
     * @return AccountSubscriberSubjectMock
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return AccountSubscriberViewMock
     */
    public function getView()
    {
        return $this->subject->View();
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function get($string)
    {
        return $this->$string;
    }
}

class AccountSubscriberSubjectMock
{
    /**
     * @var AccountSubscriberViewMock
     */
    public $view;

    /**
     * @param AccountSubscriberViewMock $view
     */
    public function __construct(AccountSubscriberViewMock $view)
    {
        $this->view = $view;
    }

    /**
     * @return AccountSubscriberViewMock
     */
    public function View()
    {
        return $this->view;
    }

    public function Request()
    {
        return new AccountRequestMock();
    }
}

class AccountRequestMock
{
    /**
     * @return string
     */
    public function getActionName()
    {
        return 'logout';
    }
}

class AccountSubscriberViewMock
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
